<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\LocationFee;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LocationFee> */
class LocationFeeFactory extends Factory
{
    protected $model = LocationFee::class;

    public function definition(): array
    {
        return [
            'pickup_location_id' => Location::factory(),
            'dropoff_location_id' => Location::factory(),
            'cost_cents' => fake()->numberBetween(1000, 8000),
            'multiply_by_days' => fake()->boolean(20),
            'tax_rate_id' => TaxRate::factory(),
            'apply_inverted' => false,
            'day_overrides' => null,
            'is_one_way_fee' => fake()->boolean(40),
            'is_active' => true,
        ];
    }
}
