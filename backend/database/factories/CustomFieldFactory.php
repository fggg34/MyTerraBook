<?php

namespace Database\Factories;

use App\Models\CustomField;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CustomField> */
class CustomFieldFactory extends Factory
{
    protected $model = CustomField::class;

    public function definition(): array
    {
        $label = fake()->randomElement(['Flight Number', 'Hotel Name', 'Passport Number', 'Company VAT']);

        return [
            'field_key' => str()->slug($label, '_'),
            'label' => $label,
            'type' => fake()->randomElement(['text', 'email', 'select', 'textarea']),
            'is_required' => fake()->boolean(30),
            'is_email' => false,
            'popup_link_url' => null,
            'select_options' => null,
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
