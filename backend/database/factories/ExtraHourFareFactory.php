<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\ExtraHourFare;
use App\Models\PriceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ExtraHourFare> */
class ExtraHourFareFactory extends Factory
{
    protected $model = ExtraHourFare::class;

    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'price_type_id' => PriceType::factory(),
            'charge_per_extra_hour_cents' => fake()->numberBetween(500, 2500),
        ];
    }
}
