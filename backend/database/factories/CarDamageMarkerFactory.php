<?php

namespace Database\Factories;

use App\Models\CarUnit;
use App\Models\CarDamageMarker;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CarDamageMarker> */
class CarDamageMarkerFactory extends Factory
{
    protected $model = CarDamageMarker::class;

    public function definition(): array
    {
        return [
            'car_unit_id' => CarUnit::factory(),
            'diagram_key' => 'default',
            'position_x' => fake()->randomFloat(2, 5, 95),
            'position_y' => fake()->randomFloat(2, 5, 95),
            'description' => fake()->randomElement([
                'Minor scratch on bumper', 'Small dent on door', 'Chip on windshield',
                'Scuff on wheel arch', 'Paint touch-up on hood',
            ]),
            'icon_path' => null,
            'marked_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
