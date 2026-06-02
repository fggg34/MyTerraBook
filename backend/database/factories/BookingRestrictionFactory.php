<?php

namespace Database\Factories;

use App\Models\BookingRestriction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BookingRestriction> */
class BookingRestrictionFactory extends Factory
{
    protected $model = BookingRestriction::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Peak Season', 'Holiday Minimum', 'Summer Restriction']),
            'date_from' => now()->startOfMonth(),
            'date_to' => now()->addMonths(2)->endOfMonth(),
            'min_rental_days' => fake()->optional(0.7)->numberBetween(2, 7),
            'max_rental_days' => fake()->optional(0.3)->numberBetween(14, 30),
            'cta_weekdays' => null,
            'ctd_weekdays' => null,
            'forced_pickup_weekdays' => null,
            'min_length_multiplier' => null,
            'is_active' => true,
        ];
    }
}
