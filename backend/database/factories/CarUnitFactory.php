<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CarUnit> */
class CarUnitFactory extends Factory
{
    protected $model = CarUnit::class;

    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
