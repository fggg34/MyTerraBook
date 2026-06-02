<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\Car;
use App\Models\CarUnit;
use App\Models\Location;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $pickupAt = fake()->dateTimeBetween('-30 days', '+14 days');
        $dropoffAt = (clone $pickupAt)->modify('+'.fake()->numberBetween(1, 10).' days');
        $baseRental = fake()->numberBetween(8000, 45000);
        $extras = fake()->numberBetween(0, 5000);
        $fees = fake()->numberBetween(0, 3000);
        $discount = fake()->numberBetween(0, 2000);
        $tax = (int) round(($baseRental + $extras + $fees - $discount) * 0.1);

        return [
            'user_id' => User::factory(),
            'car_id' => Car::factory(),
            'car_unit_id' => null,
            'price_type_id' => PriceType::factory(),
            'pickup_location_id' => Location::factory(),
            'dropoff_location_id' => Location::factory(),
            'pickup_at' => $pickupAt,
            'dropoff_at' => $dropoffAt,
            'order_status' => OrderStatus::Pending,
            'rental_status' => null,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->e164PhoneNumber(),
            'customer_country' => fake()->countryCode(),
            'base_rental_cents' => $baseRental,
            'extras_cents' => $extras,
            'fees_cents' => $fees,
            'discount_cents' => $discount,
            'tax_cents' => $tax,
            'total_cents' => $baseRental + $extras + $fees - $discount + $tax,
            'currency' => 'EUR',
            'coupon_id' => null,
            'pricing_snapshot' => null,
            'custom_field_values' => null,
            'notes' => fake()->optional(0.3)->sentence(),
            'admin_internal_note' => null,
            'created_by_admin_id' => null,
            'payment_lock_expires_at' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'order_status' => OrderStatus::Pending,
            'rental_status' => null,
        ]);
    }

    public function standBy(): static
    {
        return $this->state(fn () => [
            'order_status' => OrderStatus::StandBy,
            'rental_status' => null,
        ]);
    }

    public function confirmedUpcoming(): static
    {
        return $this->state(fn () => [
            'order_status' => OrderStatus::Confirmed,
            'rental_status' => RentalStatus::Upcoming,
            'pickup_at' => now()->addDays(fake()->numberBetween(1, 7)),
            'dropoff_at' => now()->addDays(fake()->numberBetween(8, 14)),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'order_status' => OrderStatus::Confirmed,
            'rental_status' => RentalStatus::Started,
            'pickup_at' => now()->subDays(fake()->numberBetween(1, 3)),
            'dropoff_at' => now()->addDays(fake()->numberBetween(1, 5)),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'order_status' => OrderStatus::Confirmed,
            'rental_status' => RentalStatus::Terminated,
            'pickup_at' => now()->subDays(fake()->numberBetween(10, 30)),
            'dropoff_at' => now()->subDays(fake()->numberBetween(1, 9)),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'order_status' => OrderStatus::Cancelled,
            'rental_status' => null,
        ]);
    }
}
