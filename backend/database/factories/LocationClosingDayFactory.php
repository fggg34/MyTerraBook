<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\LocationClosingDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LocationClosingDay> */
class LocationClosingDayFactory extends Factory
{
    protected $model = LocationClosingDay::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'specific_date' => fake()->optional(0.6)->dateTimeBetween('now', '+6 months')?->format('Y-m-d'),
            'recurring_weekday' => fake()->optional(0.4)->numberBetween(0, 6),
        ];
    }
}
