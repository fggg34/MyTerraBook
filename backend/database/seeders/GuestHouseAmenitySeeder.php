<?php

namespace Database\Seeders;

use App\Models\GuestHouseAmenity;
use Illuminate\Database\Seeder;

class GuestHouseAmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            ['name' => 'WiFi', 'icon' => 'wifi', 'group' => 'general'],
            ['name' => 'Air Conditioning', 'icon' => 'wind', 'group' => 'general'],
            ['name' => 'Kitchen', 'icon' => 'utensils', 'group' => 'kitchen'],
            ['name' => 'Pool', 'icon' => 'waves', 'group' => 'outdoor'],
            ['name' => 'Parking', 'icon' => 'car', 'group' => 'general'],
            ['name' => 'Washing Machine', 'icon' => 'shirt', 'group' => 'bathroom'],
            ['name' => 'TV', 'icon' => 'tv', 'group' => 'general'],
            ['name' => 'Balcony', 'icon' => 'home', 'group' => 'outdoor'],
            ['name' => 'Garden', 'icon' => 'trees', 'group' => 'outdoor'],
            ['name' => 'BBQ', 'icon' => 'flame', 'group' => 'outdoor'],
            ['name' => 'Heating', 'icon' => 'thermometer', 'group' => 'general'],
            ['name' => 'Dryer', 'icon' => 'wind', 'group' => 'bathroom'],
            ['name' => 'Iron', 'icon' => 'shirt', 'group' => 'bathroom'],
            ['name' => 'Hair Dryer', 'icon' => 'wind', 'group' => 'bathroom'],
            ['name' => 'Coffee Maker', 'icon' => 'coffee', 'group' => 'kitchen'],
            ['name' => 'Dishwasher', 'icon' => 'utensils', 'group' => 'kitchen'],
            ['name' => 'Hot Tub', 'icon' => 'bath', 'group' => 'outdoor'],
            ['name' => 'Gym', 'icon' => 'dumbbell', 'group' => 'general'],
            ['name' => 'Pet Friendly', 'icon' => 'dog', 'group' => 'general'],
            ['name' => 'Workspace', 'icon' => 'laptop', 'group' => 'general'],
            ['name' => 'Fireplace', 'icon' => 'flame', 'group' => 'general'],
            ['name' => 'Sea View', 'icon' => 'waves', 'group' => 'outdoor'],
        ];

        foreach ($amenities as $amenity) {
            GuestHouseAmenity::query()->firstOrCreate(
                ['name' => $amenity['name']],
                $amenity,
            );
        }
    }
}
