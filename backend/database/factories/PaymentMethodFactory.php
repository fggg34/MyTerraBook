<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentMethod> */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        $code = fake()->unique()->randomElement(['card', 'cash', 'bank_transfer', 'paypal', 'stripe']);

        return [
            'code' => $code,
            'name' => ucfirst(str_replace('_', ' ', $code)),
            'is_enabled' => true,
            'auto_confirm_order' => $code === 'card',
            'charge_or_discount' => 'none',
            'charge_discount_type' => null,
            'charge_fixed_cents' => null,
            'charge_percent_bips' => null,
            'config' => null,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
