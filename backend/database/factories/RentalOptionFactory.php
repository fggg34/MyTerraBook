<?php

namespace Database\Factories;

use App\Models\RentalOption;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RentalOption> */
class RentalOptionFactory extends Factory
{
    protected $model = RentalOption::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Full Insurance', 'GPS Device', 'Child Seat', 'Additional Driver',
            'Snow Chains', 'Roof Rack', 'Wi-Fi Hotspot', 'Roadside Assistance Plus',
        ]);

        return [
            'name' => $name,
            'description' => fake()->optional(0.6)->sentence(10),
            'cost_cents' => fake()->randomElement([500, 800, 1000, 1500, 2000, 2500, 3500]),
            'is_daily_cost' => fake()->boolean(70),
            'max_cost_cap_cents' => fake()->optional(0.3)->randomElement([5000, 7500, 10000]),
            'min_rental_days' => null,
            'max_rental_days' => null,
            'sort_order' => fake()->numberBetween(0, 20),
            'tax_rate_id' => TaxRate::factory(),
            'has_quantity' => fake()->boolean(20),
            'is_mandatory' => false,
            'is_active' => true,
        ];
    }
}
