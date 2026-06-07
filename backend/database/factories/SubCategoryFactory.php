<?php

namespace Database\Factories;

use App\Models\MainCategory;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SubCategory> */
class SubCategoryFactory extends Factory
{
    protected $model = SubCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Hatchback', 'Sedan', 'Estate', 'SUV', 'Convertible', 'Economy', 'Compact', 'Van',
        ]);

        return [
            'main_category_id' => MainCategory::factory(),
            'name' => $name,
            'description' => fake()->optional(0.7)->sentence(12),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'is_search_filter' => true,
        ];
    }
}
