<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\HourlyFare;
use App\Models\PriceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<HourlyFare> */
class HourlyFareFactory extends Factory
{
    protected $model = HourlyFare::class;

    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'price_type_id' => PriceType::factory(),
            'min_minutes' => 60,
            'max_minutes' => 480,
            'total_price_cents' => fake()->numberBetween(1500, 8000),
        ];
    }
}
