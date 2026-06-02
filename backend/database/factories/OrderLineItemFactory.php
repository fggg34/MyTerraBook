<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderLineItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderLineItem> */
class OrderLineItemFactory extends Factory
{
    protected $model = OrderLineItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'kind' => fake()->randomElement(['rental', 'extra', 'fee', 'discount', 'tax']),
            'label' => fake()->words(3, true),
            'amount_cents' => fake()->numberBetween(500, 20000),
            'quantity' => 1,
            'meta' => null,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
