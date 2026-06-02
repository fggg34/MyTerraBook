<?php

namespace Database\Factories;

use App\Models\PriceType;
use App\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PriceType> */
class PriceTypeFactory extends Factory
{
    protected $model = PriceType::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Standard Rate', 'Premium Rate', 'Weekend Rate', 'Long-term Rate', 'Corporate Rate',
        ]);

        return [
            'name' => $name,
            'attribute_label' => fake()->optional(0.4)->randomElement(['Insurance', 'Mileage', 'Driver age']),
            'attribute_value_per_day' => fake()->optional(0.3)->randomElement(['Unlimited', '200 km', '25+']),
            'tax_rate_id' => TaxRate::factory(),
            'is_active' => true,
        ];
    }
}
