<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarDistinctiveFeatureDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CarDistinctiveFeatureDefinition> */
class CarDistinctiveFeatureDefinitionFactory extends Factory
{
    protected $model = CarDistinctiveFeatureDefinition::class;

    public function definition(): array
    {
        return [
            'car_id' => Car::factory(),
            'name' => fake()->randomElement(['License Plate', 'VIN', 'Color', 'Fleet Number']),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
