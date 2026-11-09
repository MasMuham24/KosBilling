<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransWebhookReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected function createBillWithRelations(string $status = 'unpaid'): Bill
    {
        $user = User::factory()->create();
        $property = Property::create([
            'user_id' => $user->id,
            'name' => 'Kost Harmoni',
            'address' => 'Jl. Harmoni Indah No. 5',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '105',
            'monthly_price' => 2000000,
            'status' => 'occupied',
        ]);
        $tenant = Tenant::create([
            'room_id' => $room->id,
            'name' => 'Dewi Lestari',
            'whatsapp_number' => '081299988877',
            'due_date_day' => 10,
            'checkin_in_date' => '2026-01-01',
            'status' => 'active',
        ]);

        return Bill::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'bill_month' => '2026-11',
            'base_amount' => 2000000,
            'additional_charge' => 0,
            'total_amount' => 2000000,
            'status' => $status,
            'due_date' => '2026-11-10',
        ]);
    }

    public function test_bill_builds_receipt_message_with_required_structure(): void
    {
        $bill = $this->createBillWithRelations('paid');
        Payment::create([
            'bill_id' => $bill->id,
            'order_id' => "BILL-{$bill->id}-123456",
            'transaction_id' => 'midtrans-trx-001',
            'amount_paid' => 2000000,
            'payment_type' => 'qris',
            'transaction_status' => 'settlement',
            'verified_at' => now(),
        ]);

        $bill->load(['tenant', 'room.property', 'payment']);
        $receipt = $bill->buildReceiptMessage();

        $this->assertStringContainsString('BUKTI PEMBAYARAN RESMI', $receipt);
        $this->assertStringContainsString("BILL-#{$bill->id}", $receipt);
        $this->assertStringContainsString('Kost Harmoni', $receipt);
        $this->assertStringContainsString('105', $receipt);
        $this->assertStringContainsString('November 2026', $receipt);
        $this->assertStringContainsString('QRIS', $receipt);
        $this->assertStringContainsString('2.000.000', $receipt);
        $this->assertStringContainsString('WIB', $receipt);
        $this->assertStringContainsString(route('invoice.show', $bill->id), $receipt);
    }

    public function test_midtrans_webhook_processes_settlement_and_dispatches_receipt(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/send-message' => Http::response(['status' => true], 200),
        ]);

        $bill = $this->createBillWithRelations('unpaid');
        $orderId = "BILL-{$bill->id}-" . time();
        $serverKey = 'test-server-key';
        Config::set('midtrans.server_key', $serverKey);

        $statusCode = '200';
        $grossAmount = '2000000.00';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'gopay',
            'transaction_id' => 'trx-12345',
        ];

        $response = $this->postJson('/api/midtrans-callback', $payload);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Webhook handled successfully']);

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertNotNull($bill->payment);
        $this->assertEquals('settlement', $bill->payment->transaction_status);
        $this->assertNotNull($bill->payment->verified_at);

        Http::assertSent(function ($request) use ($bill) {
            return $request->url() === 'http://127.0.0.1:3000/api/send-message'
                && $request['target'] === $bill->tenant->whatsapp_number
                && str_contains($request['message'], "BILL-#{$bill->id}")
                && str_contains($request['message'], 'BUKTI PEMBAYARAN RESMI');
        });
    }

    public function test_midtrans_webhook_processes_capture_accept_and_dispatches_receipt(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/send-message' => Http::response(['status' => true], 200),
        ]);

        $bill = $this->createBillWithRelations('unpaid');
        $orderId = "BILL-{$bill->id}-" . time();
        $serverKey = 'test-server-key';
        Config::set('midtrans.server_key', $serverKey);

        $statusCode = '200';
        $grossAmount = '2000000.00';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'capture',
            'fraud_status' => 'accept',
            'payment_type' => 'credit_card',
            'transaction_id' => 'trx-cc-999',
        ];

        $response = $this->postJson('/api/midtrans-callback', $payload);

        $response->assertStatus(200);

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        Http::assertSentCount(1);
    }

    public function test_midtrans_webhook_does_not_resend_receipt_if_already_paid(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/send-message' => Http::response(['status' => true], 200),
        ]);

        $bill = $this->createBillWithRelations('paid');
        $orderId = "BILL-{$bill->id}-" . time();
        $serverKey = 'test-server-key';
        Config::set('midtrans.server_key', $serverKey);

        $statusCode = '200';
        $grossAmount = '2000000.00';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'trx-duplicate',
        ];

        $response = $this->postJson('/api/midtrans-callback', $payload);

        $response->assertStatus(200);
        // Should not send receipt if bill was already paid
        Http::assertNothingSent();
    }

    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        $bill = $this->createBillWithRelations('unpaid');
        $orderId = "BILL-{$bill->id}-" . time();
        Config::set('midtrans.server_key', 'real-server-key');

        $payload = [
            'order_id' => $orderId,
            'status_code' => '200',
            'gross_amount' => '2000000.00',
            'signature_key' => 'invalid-hash-here',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'trx-sig-fail',
        ];

        $response = $this->postJson('/api/midtrans-callback', $payload);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Invalid signature key']);

        $bill->refresh();
        $this->assertEquals('unpaid', $bill->status);
    }

    public function test_midtrans_webhook_succeeds_even_if_whatsapp_service_fails(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/send-message' => Http::response(['status' => false], 500),
        ]);

        $bill = $this->createBillWithRelations('unpaid');
        $orderId = "BILL-{$bill->id}-" . time();
        $serverKey = 'test-server-key';
        Config::set('midtrans.server_key', $serverKey);

        $statusCode = '200';
        $grossAmount = '2000000.00';
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'trx-gateway-fail',
        ];

        $response = $this->postJson('/api/midtrans-callback', $payload);

        // Webhook must STILL return HTTP 200 and update database
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Webhook handled successfully']);

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertEquals('settlement', $bill->payment->transaction_status);
    }
}

