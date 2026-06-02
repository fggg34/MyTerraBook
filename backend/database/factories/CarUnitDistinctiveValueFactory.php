<?php

namespace Database\Factories;

use App\Models\CarDistinctiveFeatureDefinition;
use App\Models\CarUnit;
use App\Models\CarUnitDistinctiveValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CarUnitDistinctiveValue> */
class CarUnitDistinctiveValueFactory extends Factory
{
    protected $model = CarUnitDistinctiveValue::class;

    public function definition(): array
    {
        return [
            'car_unit_id' => CarUnit::factory(),
            'car_distinctive_feature_definition_id' => CarDistinctiveFeatureDefinition::factory(),
            'value' => fake()->bothify('??-###-??'),
        ];
    }
}
