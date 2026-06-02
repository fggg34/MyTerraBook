<?php

namespace Database\Factories;

use App\Models\AvailabilityBlock;
use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AvailabilityBlock> */
class AvailabilityBlockFactory extends Factory
{
    protected $model = AvailabilityBlock::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('now', '+30 days');
        $endsAt = (clone $startsAt)->modify('+'.fake()->numberBetween(1, 5).' days');

        return [
            'car_id' => Car::factory(),
            'source' => fake()->randomElement(['manual_close', 'maintenance', 'external_ical']),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'units_blocked' => 1,
            'external_uid' => null,
            'external_calendar' => null,
            'notes' => fake()->optional(0.4)->sentence(),
            'is_active' => true,
        ];
    }
}
