<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::first();
        if (!$owner) {
            return;
        }

        $properties = [
            ['name' => 'Kos Griya Nara Asri 1', 'address' => 'Jl. Veteran No. 12, Semarang Selatan, Kota Semarang'],
            ['name' => 'Kos Griya Nara Asri 2', 'address' => 'Jl. Pleburan Barat No. 45, Semarang Selatan, Kota Semarang'],
            ['name' => 'Kos Nara Katonsari Eksekutif', 'address' => 'Jl. Katonsari Raya No. 8, Demak'],
            ['name' => 'Kos Nara Tembalang Undip', 'address' => 'Jl. Banjarsari Selatan No. 21, Tembalang, Kota Semarang'],
            ['name' => 'Kos Nara Sekaran Unnes', 'address' => 'Jl. Taman Siswa No. 15, Gunungpati, Kota Semarang'],
        ];
        foreach ($properties as $prop) {
            Property::create([
                'user_id' => $owner->id,
                'name' => $prop['name'],
                'address' => $prop['address'],
            ]);
        }

        foreach ($properties as $prop) {
            Property::create([
                'user_id' => $owner->id,
                'name' => $prop['name'],
                'address' => $prop['address'],
            ]);
        }
    }
}
