<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BillSeeder extends Seeder
{
    public function run(): void
    {
        $currentMonth = date('Y-m');
        $tenants = Tenant::with('room')->get();

        foreach ($tenants as $index => $tenant) {
            $baseAmount = $tenant->room->monthly_price;
            $extraCharge = ($index % 3 == 0) ? 50000 : 0; // Biaya tambahan utilitas
            $totalAmount = $baseAmount + $extraCharge;
            $dueDate = sprintf('%s-%02d', $currentMonth, min($tenant->due_date_day, 28));

            // Variasi status: 10 Lunas, 3 Verifikasi, 3 Belum Bayar
            if ($index < 10) {
                $status = 'paid';
            } elseif ($index < 13) {
                $status = 'pending_verification';
            } else {
                $status = 'unpaid';
            }

            $bill = Bill::create([
                'tenant_id' => $tenant->id,
                'room_id' => $tenant->room_id,
                'bill_month' => $currentMonth,
                'base_amount' => $baseAmount,
                'additional_charge' => $extraCharge,
                'total_amount' => $totalAmount,
                'status' => $status,
                'due_date' => $dueDate,
            ]);

            // Untuk tagihan yang 'paid', buatkan riwayat payment
            if ($status === 'paid') {
                $methods = ['qris', 'bca_va', 'mandiri_va', 'gopay', 'manual_transfer'];
                $selectedMethod = $methods[$index % count($methods)];

                Payment::create([
                    'bill_id' => $bill->id,
                    'order_id' => 'BILL-' . $bill->id . '-' . time() . '-' . $index,
                    'transaction_id' => 'TRX-' . strtoupper(bin2hex(random_bytes(5))),
                    'amount_paid' => $totalAmount,
                    'payment_type' => $selectedMethod,
                    'transaction_status' => 'settlement',
                    'raw_response' => json_encode([
                        'status' => 'settlement',
                        'payment_type' => $selectedMethod,
                        'gross_amount' => (string) $totalAmount,
                    ]),
                    'verified_at' => Carbon::now()->subDays($index % 10),
                ]);
            }
        }
    }
}
