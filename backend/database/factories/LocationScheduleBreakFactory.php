<?php

namespace Database\Factories;

use App\Models\LocationSchedule;
use App\Models\LocationScheduleBreak;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LocationScheduleBreak> */
class LocationScheduleBreakFactory extends Factory
{
    protected $model = LocationScheduleBreak::class;

    public function definition(): array
    {
        return [
            'location_schedule_id' => LocationSchedule::factory(),
            'break_start' => '13:00:00',
            'break_end' => '14:00:00',
        ];
    }
}
