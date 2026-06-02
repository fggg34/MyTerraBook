<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Location> */
class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Tirana Airport (TIA)', 'Tirana City Center', 'Durres Port',
            'Vlorë Downtown', 'Shkodër Station', 'Saranda Marina',
            'Berat Old Town', 'Korçë Center',
        ]);

        return [
            'name' => $name,
            'address' => fake()->streetAddress().', '.fake()->city(),
            'latitude' => fake()->latitude(39.5, 42.5),
            'longitude' => fake()->longitude(19.0, 21.5),
            'tax_rate_id' => TaxRate::factory(),
            'description' => fake()->optional(0.5)->paragraph(),
            'default_opening_time' => '08:00:00',
            'default_closing_time' => '20:00:00',
            'suggested_preselected_time' => '10:00:00',
            'is_active' => true,
        ];
    }
}
