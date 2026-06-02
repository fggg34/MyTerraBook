<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderRentalOption;
use App\Models\RentalOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderRentalOption> */
class OrderRentalOptionFactory extends Factory
{
    protected $model = OrderRentalOption::class;

    public function definition(): array
    {
        $unitPrice = fake()->numberBetween(500, 3500);
        $quantity = fake()->numberBetween(1, 2);

        return [
            'order_id' => Order::factory(),
            'rental_option_id' => RentalOption::factory(),
            'quantity' => $quantity,
            'unit_price_cents' => $unitPrice,
            'total_cents' => $unitPrice * $quantity,
        ];
    }
}
