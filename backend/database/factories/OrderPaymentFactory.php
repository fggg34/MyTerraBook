<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderPayment> */
class OrderPaymentFactory extends Factory
{
    protected $model = OrderPayment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'amount_cents' => fake()->numberBetween(5000, 50000),
            'method_code' => fake()->randomElement(['card', 'cash', 'bank_transfer', 'paypal']),
            'status' => fake()->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'transaction_ref' => fake()->optional(0.8)->uuid(),
            'meta' => null,
            'processed_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }
}
