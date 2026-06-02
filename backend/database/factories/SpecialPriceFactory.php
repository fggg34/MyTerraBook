<?php

namespace Database\Factories;

use App\Models\SpecialPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SpecialPrice> */
class SpecialPriceFactory extends Factory
{
    protected $model = SpecialPrice::class;

    public function definition(): array
    {
        $isPercent = fake()->boolean();

        return [
            'name' => fake()->randomElement(['Summer Sale', 'Winter Discount', 'Early Bird', 'Weekend Special']),
            'date_from' => now()->subMonth(),
            'date_to' => now()->addMonths(3),
            'weekdays' => null,
            'type' => 'discount',
            'value_mode' => $isPercent ? 'percentage' : 'fixed',
            'value_fixed_cents' => $isPercent ? null : fake()->numberBetween(500, 3000),
            'value_percent_bips' => $isPercent ? fake()->randomElement([500, 1000, 1500]) : null,
            'day_overrides' => null,
            'vehicle_ids' => null,
            'price_type_ids' => null,
            'pickup_location_ids' => null,
            'dropoff_location_ids' => null,
            'apply_after_season_start' => false,
            'lock_first_day_rate' => false,
            'round_to_integer' => false,
            'year' => (int) now()->format('Y'),
            'is_promotion' => fake()->boolean(50),
            'is_active' => true,
        ];
    }
}
