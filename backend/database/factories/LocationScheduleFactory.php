<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\LocationSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LocationSchedule> */
class LocationScheduleFactory extends Factory
{
    protected $model = LocationSchedule::class;

    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'weekday' => fake()->numberBetween(0, 6),
            'opening_time' => '08:00:00',
            'closing_time' => '20:00:00',
            'is_closed' => false,
        ];
    }
}
