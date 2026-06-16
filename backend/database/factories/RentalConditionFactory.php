<?php

namespace Database\Factories;

use App\Models\RentalCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RentalCondition> */
class RentalConditionFactory extends Factory
{
    protected $model = RentalCondition::class;

    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'name' => $title,
            'title' => $title,
            'description' => fake()->sentence(),
            'icon' => 'check',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
