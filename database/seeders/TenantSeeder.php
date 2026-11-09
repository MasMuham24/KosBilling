<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Isi 16 kamar pertama dengan tenant aktif, sisanya tetap 'available'
        $rooms = Room::take(16)->get();

        $names = [
            'Budi Santoso', 'Siti Rahmawati', 'Dimas Pratama', 'Aulia Zahra',
            'Rizky Ramadhan', 'Anisa Putri', 'Fajar Hidayat', 'Dewi Lestari',
            'Ilham Maulana', 'Nabila Maharani', 'Hendra Kusuma', 'Mega Utami',
            'Agus Setiawan', 'Tiara Andini', 'Bayu Saputra', 'Rina Wulandari',
        ];

        foreach ($rooms as $index => $room) {
            $checkInDay = ($index % 25) + 1;
            $dueDay = ($index % 28) + 1;

            Tenant::create([
                'room_id' => $room->id,
                'name' => $names[$index] ?? ('Penyewa ' . ($index + 1)),
                'whatsapp_number' => '0812' . str_pad($index + 10000000, 8, '0', STR_PAD_LEFT),
                'checkin_in_date' => sprintf('2026-08-%02d', $checkInDay),
                'due_date_day' => $dueDay,
                'status' => 'active',
            ]);

            // Kamar yang diisi penyewa aktif ditandai 'occupied'
            $room->update(['status' => 'occupied']);
        }
    }
}
