<?php

namespace Database\Factories;

use App\Models\Characteristic;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Characteristic> */
class CharacteristicFactory extends Factory
{
    protected $model = Characteristic::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Air Conditioning', 'GPS Navigation', 'Bluetooth', 'USB Port',
            'Child Seat', 'Automatic Transmission', '4WD', 'Cruise Control',
            'Parking Sensors', 'Backup Camera', 'Heated Seats', 'Sunroof',
        ]);

        return [
            'name' => $name,
            'display_text' => $name,
            'icon_path' => null,
            'sort_order' => fake()->numberBetween(0, 50),
            'is_search_filter' => fake()->boolean(60),
        ];
    }
}
