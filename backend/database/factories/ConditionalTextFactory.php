<?php

namespace Database\Factories;

use App\Models\ConditionalText;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConditionalText> */
class ConditionalTextFactory extends Factory
{
    protected $model = ConditionalText::class;

    public function definition(): array
    {
        $content = fake()->paragraph();

        return [
            'name' => fake()->words(3, true),
            'content' => "<p>{$content}</p>",
            'content_plain' => $content,
            'conditions' => ['min_days' => 3],
            'templates' => ['checkout'],
            'placement' => 'body',
            'is_active' => true,
        ];
    }
}
