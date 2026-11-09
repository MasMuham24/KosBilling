<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Property;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_whatsapp_service_sends_post_request_successfully(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/send-message' => Http::response(['status' => true], 200),
        ]);

        $service = new WhatsAppService();
        $result = $service->sendMessage('08123456789', 'Hello world');

        $this->assertTrue($result);
        Http::assertSent(function ($request) {
            return $request->url() === 'http://127.0.0.1:3000/api/send-message'
                && $request['target'] === '08123456789'
                && $request['message'] === 'Hello world';
        });
    }

    public function test_whatsapp_service_returns_false_on_failure(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/send-message' => Http::response(['status' => false], 500),
        ]);

        $service = new WhatsAppService();
        $result = $service->sendMessage('08123456789', 'Hello world');

        $this->assertFalse($result);
    }

    public function test_bill_builds_reminder_message_with_expected_content(): void
    {
        $user = User::factory()->create();
        $property = Property::create([
            'user_id' => $user->id,
            'name' => 'Kost Melati Indah',
            'address' => 'Jl. Mawar No. 12',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '101',
            'monthly_price' => 1500000,
            'status' => 'occupied',
        ]);
        $tenant = Tenant::create([
            'room_id' => $room->id,
            'name' => 'Budi Santoso',
            'whatsapp_number' => '081234567890',
            'due_date_day' => 15,
            'checkin_in_date' => '2026-01-01',
            'status' => 'active',
        ]);
        $bill = Bill::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'bill_month' => '2026-11',
            'base_amount' => 1500000,
            'additional_charge' => 0,
            'total_amount' => 1500000,
            'status' => 'unpaid',
            'due_date' => '2026-11-15',
        ]);

        $message = $bill->buildReminderMessage();

        $this->assertStringContainsString('Budi Santoso', $message);
        $this->assertStringContainsString('Kost Melati Indah', $message);
        $this->assertStringContainsString('101', $message);
        $this->assertStringContainsString('November 2026', $message);
        $this->assertStringContainsString('15 November 2026', $message);
        $this->assertStringContainsString('1.500.000', $message);
        $this->assertStringContainsString(route('invoice.show', $bill->id), $message);
    }

    public function test_user_can_send_reminder_for_their_property_bill(): void
    {
        Http::fake([
            'http://127.0.0.1:3000/api/send-message' => Http::response(['status' => true], 200),
        ]);

        $user = User::factory()->create();
        $property = Property::create([
            'user_id' => $user->id,
            'name' => 'Kost Cempaka',
            'address' => 'Jl. Cempaka',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '202',
            'monthly_price' => 1000000,
            'status' => 'occupied',
        ]);
        $tenant = Tenant::create([
            'room_id' => $room->id,
            'name' => 'Siti Nurhaliza',
            'whatsapp_number' => '08987654321',
            'due_date_day' => 10,
            'checkin_in_date' => '2026-01-01',
            'status' => 'active',
        ]);
        $bill = Bill::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'bill_month' => '2026-11',
            'base_amount' => 1000000,
            'additional_charge' => 0,
            'total_amount' => 1000000,
            'status' => 'unpaid',
            'due_date' => '2026-11-10',
        ]);

        $response = $this->actingAs($user)->post(route('bills.sendReminder', $bill));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Notifikasi WhatsApp berhasil dikirim ke Siti Nurhaliza');
    }

    public function test_user_cannot_send_reminder_for_other_users_bill(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $property = Property::create([
            'user_id' => $owner->id,
            'name' => 'Kost Pribadi',
            'address' => 'Jl. Melati',
        ]);
        $room = Room::create([
            'property_id' => $property->id,
            'room_number' => '303',
            'monthly_price' => 1200000,
            'status' => 'occupied',
        ]);
        $tenant = Tenant::create([
            'room_id' => $room->id,
            'name' => 'Ahmad Dani',
            'whatsapp_number' => '08111222333',
            'due_date_day' => 5,
            'checkin_in_date' => '2026-01-01',
            'status' => 'active',
        ]);
        $bill = Bill::create([
            'tenant_id' => $tenant->id,
            'room_id' => $room->id,
            'bill_month' => '2026-11',
            'base_amount' => 1200000,
            'additional_charge' => 0,
            'total_amount' => 1200000,
            'status' => 'unpaid',
            'due_date' => '2026-11-05',
        ]);

        $response = $this->actingAs($otherUser)->post(route('bills.sendReminder', $bill));

        $response->assertStatus(403);
    }
}
