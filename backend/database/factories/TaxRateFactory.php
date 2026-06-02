<?php

namespace Database\Factories;

use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TaxRate> */
class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Standard VAT', 'Reduced VAT', 'Zero VAT']),
            'basis_points' => fake()->randomElement([0, 600, 1000, 2000]),
        ];
    }
}
