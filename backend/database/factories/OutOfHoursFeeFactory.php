<?php

namespace Database\Factories;

use App\Models\OutOfHoursFee;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OutOfHoursFee> */
class OutOfHoursFeeFactory extends Factory
{
    protected $model = OutOfHoursFee::class;

    public function definition(): array
    {
        return [
            'name' => 'After-hours surcharge',
            'time_from' => '20:00:00',
            'time_to' => '08:00:00',
            'applies_to' => fake()->randomElement(['pickup', 'dropoff', 'both']),
            'cost_cents' => fake()->numberBetween(1500, 4000),
            'pickup_cost_cents' => null,
            'dropoff_cost_cents' => null,
            'max_combined_charge_cents' => null,
            'tax_rate_id' => TaxRate::factory(),
            'vehicle_ids' => null,
            'location_ids' => null,
            'weekday_filter' => null,
            'is_active' => true,
        ];
    }
}
