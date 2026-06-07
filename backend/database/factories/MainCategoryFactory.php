<?php

namespace Database\Factories;

use App\Models\MainCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MainCategory> */
class MainCategoryFactory extends Factory
{
    protected $model = MainCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Car', 'Campervan']);

        return [
            'name' => $name,
            'description' => fake()->optional(0.7)->sentence(12),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
