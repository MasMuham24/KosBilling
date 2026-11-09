<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Notification;
use Throwable;

class MidtransWebhookController extends Controller
{
    /**
     * Handle incoming payment notifications from Midtrans.
     */
    public function handle(Request $request, WhatsAppService $waService)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        if (app()->environment('testing')) {
            $notif = (object) $request->all();
        } else {
            try {
                $notif = new Notification();
            } catch (Throwable $e) {
                // Fallback to request payload directly if Notification() cannot reach Midtrans status API
                $payload = $request->all();
                if (!empty($payload['order_id'])) {
                    $notif = (object) $payload;
                } else {
                    return response()->json(['message' => 'Invalid notification: ' . $e->getMessage()], 400);
                }
            }
        }

        $orderId = $notif->order_id ?? $request->input('order_id');
        $statusCode = $notif->status_code ?? $request->input('status_code');
        $grossAmount = $notif->gross_amount ?? $request->input('gross_amount');
        $signatureKey = $notif->signature_key ?? $request->input('signature_key');
        $serverKey = config('midtrans.server_key');

        // Verify Midtrans signature key if provided
        if ($signatureKey && $serverKey && $orderId && $statusCode && $grossAmount) {
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            if ($signatureKey !== $expectedSignature) {
                Log::warning("Midtrans Webhook: Invalid signature key for Order #{$orderId}");
                return response()->json(['message' => 'Invalid signature key'], 403);
            }
        }

        $transactionStatus = $notif->transaction_status ?? $request->input('transaction_status');
        $paymentType = $notif->payment_type ?? $request->input('payment_type');
        $fraudStatus = $notif->fraud_status ?? $request->input('fraud_status');
        $transactionId = $notif->transaction_id ?? $request->input('transaction_id');

        $parts = explode('-', $orderId);
        $billId = $parts[1] ?? null;

        $bill = Bill::with(['tenant', 'room.property', 'payment'])->find($billId);
        if (!$bill) {
            return response()->json(['message' => 'Bill not found'], 404);
        }

        $isSuccess = ($transactionStatus === 'settlement')
            || ($transactionStatus === 'capture' && ($fraudStatus === 'accept' || empty($fraudStatus)));
        $wasAlreadyPaid = ($bill->status === 'paid');
        $shouldSendReceipt = false;

        DB::transaction(function () use (
            $bill,
            $orderId,
            $transactionId,
            $grossAmount,
            $paymentType,
            $transactionStatus,
            $request,
            $isSuccess,
            $wasAlreadyPaid,
            &$shouldSendReceipt
        ) {
            $payment = Payment::updateOrCreate(
                ['order_id' => $orderId],
                [
                    'bill_id'            => $bill->id,
                    'transaction_id'     => $transactionId,
                    'amount_paid'        => (float) $grossAmount,
                    'payment_type'       => $paymentType,
                    'transaction_status' => $transactionStatus,
                    'raw_response'       => json_encode($request->all()),
                ]
            );

            if ($isSuccess) {
                $bill->update(['status' => 'paid']);
                $payment->update(['verified_at' => now()]);

                if (!$wasAlreadyPaid) {
                    $shouldSendReceipt = true;
                }
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
                $bill->update(['status' => 'unpaid']);
            } elseif ($transactionStatus === 'pending') {
                $bill->update(['status' => 'pending_verification']);
            }
        });

        // Post-transaction receipt dispatch
        if ($shouldSendReceipt) {
            $bill->load('payment');

            if ($bill->tenant?->whatsapp_number) {
                try {
                    $receipt = $bill->buildReceiptMessage();
                    $waService->sendMessage($bill->tenant->whatsapp_number, $receipt);
                } catch (Throwable $e) {
                    Log::error("Failed to send WhatsApp receipt for Bill #{$bill->id}: " . $e->getMessage());
                }
            }
        }

        return response()->json(['message' => 'Webhook handled successfully']);
    }
}
