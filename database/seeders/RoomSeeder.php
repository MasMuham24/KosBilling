<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $properties = Property::all();
        $roomTypes = [
            ['suffix' => 'A', 'price' => 1200000],
            ['suffix' => 'B', 'price' => 1350000],
            ['suffix' => 'C', 'price' => 1500000],
            ['suffix' => 'VIP', 'price' => 1800000],
        ];

        $counter = 1;
        foreach ($properties as $property) {
            foreach ($roomTypes as $type) {
                Room::create([
                    'property_id' => $property->id,
                    'room_number' => 'K' . str_pad($counter, 2, '0', STR_PAD_LEFT) . '-' . $type['suffix'],
                    'monthly_price' => $type['price'],
                    'status' => 'available',
                ]);
                $counter++;
            }
        }
    }
}
