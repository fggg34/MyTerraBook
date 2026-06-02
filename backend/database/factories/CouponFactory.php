<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Coupon> */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        $isPercent = fake()->boolean();

        return [
            'code' => strtoupper(fake()->unique()->bothify('??##??')),
            'type' => fake()->randomElement(['permanent', 'gift']),
            'discount_type' => $isPercent ? 'percentage' : 'fixed',
            'discount_fixed_cents' => $isPercent ? null : fake()->numberBetween(1000, 5000),
            'discount_percent_bips' => $isPercent ? fake()->randomElement([500, 1000, 1500, 2000]) : null,
            'vehicle_ids' => null,
            'valid_from' => now()->subMonth(),
            'valid_to' => now()->addMonths(6),
            'min_order_total_cents' => fake()->optional(0.5)->numberBetween(5000, 20000),
            'is_active' => true,
        ];
    }
}
