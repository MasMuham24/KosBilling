<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'programmergabut',
                'email' => 'programmergabut@web.com',
                'whatsapp_number' => '081234567890',
                'password' => Hash::make('admin123'),
            ]
        );
    }
}
