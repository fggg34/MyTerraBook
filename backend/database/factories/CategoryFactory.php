<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Economy', 'Compact', 'Mid-size', 'SUV', 'Luxury', 'Van', 'Electric', 'Convertible',
        ]);

        return [
            'name' => $name,
            'description' => fake()->optional(0.7)->sentence(12),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
