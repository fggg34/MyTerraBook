<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\DailyFare;
use App\Models\PriceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DailyFare> */
class DailyFareFactory extends Factory
{
    protected $model = DailyFare::class;

    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'price_type_id' => PriceType::factory(),
            'from_days' => 1,
            'to_days' => 30,
            'price_per_day_cents' => fake()->numberBetween(2500, 15000),
        ];
    }
}
