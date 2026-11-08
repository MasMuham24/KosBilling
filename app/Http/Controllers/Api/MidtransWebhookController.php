<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        try {
            $notif = new Notification();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid notification: ' . $e->getMessage()], 400);
        }

        $transactionStatus = $notif->transaction_status;
        $paymentType = $notif->payment_type;
        $orderId = $notif->order_id;
        $fraudStatus = $notif->fraud_status ?? null;
        $grossAmount = $notif->gross_amount;
        $transactionId = $notif->transaction_id;
        $parts = explode('-', $orderId);
        $billId = $parts[1] ?? null;

        $bill = Bill::find($billId);
        if (!$bill) {
            return response()->json(['message' => 'Bill not found'], 404);
        }

        $payment = Payment::updateOrCreate(
            ['order_id' => $orderId],
            [
                'bill_id'            => $bill->id,
                'transaction_id'     => $transactionId,
                'amount_paid'        => $grossAmount,
                'payment_type'       => $paymentType,
                'transaction_status' => $transactionStatus,
                'raw_response'       => json_encode($request->all()),
            ]
        );

        if ($transactionStatus == 'capture') {
            if ($fraudStatus == 'accept') {
                $bill->update(['status' => 'paid']);
                $payment->update(['verified_at' => now()]);
            }
        } elseif ($transactionStatus == 'settlement') {
            $bill->update(['status' => 'paid']);
            $payment->update(['verified_at' => now()]);
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $bill->update(['status' => 'unpaid']);
        } elseif ($transactionStatus == 'pending') {
            $bill->update(['status' => 'pending_verification']);
        }

        return response()->json(['message' => 'Webhook handled successfully']);
    }
}
