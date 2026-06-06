<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Models\Car;
use App\Models\CarUnit;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Location;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\OrderPayment;
use App\Models\OrderRentalOption;
use App\Models\PriceType;
use App\Models\RentalOption;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (Order::query()->exists()) {
            return;
        }

        $customers = User::query()->where('role', UserRole::Customer)->get();
        $admin = User::query()->where('role', UserRole::Admin)->first();
        $cars = Car::query()->where('is_active', true)->get();
        $locations = Location::query()->where('is_active', true)->get();
        $priceType = PriceType::query()->where('slug', 'basic')->firstOrFail();
        $gpsOption = RentalOption::query()->where('name', 'GPS Device')->first();
        $insuranceOption = RentalOption::query()->where('name', 'Full Insurance')->first();
        $welcomeCoupon = Coupon::query()->where('code', 'WELCOME10')->first();

        if ($customers->isEmpty() || $cars->isEmpty() || $locations->count() < 2) {
            return;
        }

        $scenarios = [
            ['state' => 'pending', 'count' => 2],
            ['state' => 'stand_by', 'count' => 2],
            ['state' => 'upcoming', 'count' => 3],
            ['state' => 'active', 'count' => 3],
            ['state' => 'completed', 'count' => 4],
            ['state' => 'cancelled', 'count' => 2],
        ];

        foreach ($scenarios as $scenario) {
            for ($i = 0; $i < $scenario['count']; $i++) {
                $this->createOrderForState(
                    $scenario['state'],
                    $customers->random(),
                    $cars->random(),
                    $locations->random(),
                    $locations->random(),
                    $priceType,
                    $admin,
                    $gpsOption,
                    $insuranceOption,
                    $welcomeCoupon,
                );
            }
        }
    }

    private function createOrderForState(
        string $state,
        User $customer,
        Car $car,
        Location $pickup,
        Location $dropoff,
        PriceType $priceType,
        ?User $admin,
        ?RentalOption $gpsOption,
        ?RentalOption $insuranceOption,
        ?Coupon $coupon,
    ): void {
        [$pickupAt, $dropoffAt, $orderStatus, $rentalStatus] = match ($state) {
            'pending' => [now()->addDays(5), now()->addDays(8), OrderStatus::Pending, null],
            'stand_by' => [now()->addDays(3), now()->addDays(6), OrderStatus::StandBy, null],
            'upcoming' => [now()->addDays(2), now()->addDays(7), OrderStatus::Confirmed, RentalStatus::Upcoming],
            'active' => [now()->subDays(2), now()->addDays(3), OrderStatus::Confirmed, RentalStatus::Started],
            'completed' => [now()->subDays(14), now()->subDays(7), OrderStatus::Confirmed, RentalStatus::Terminated],
            'cancelled' => [now()->addDays(10), now()->addDays(13), OrderStatus::Cancelled, null],
            default => [now()->addDays(4), now()->addDays(7), OrderStatus::Pending, null],
        };

        $days = max(1, $pickupAt->diffInDays($dropoffAt));
        $dailyRate = $car->dailyFares()
            ->where('price_type_id', $priceType->id)
            ->where('from_days', '<=', $days)
            ->where('to_days', '>=', $days)
            ->value('price_per_day_cents') ?? 5000;

        $baseRental = $dailyRate * $days;
        $extras = 0;
        $discount = 0;
        $fees = fake()->boolean(30) ? 2500 : 0;
        $tax = (int) round(($baseRental + $extras + $fees) * 0.1);
        $total = $baseRental + $extras + $fees - $discount + $tax;

        $carUnit = CarUnit::query()->where('car_id', $car->id)->where('is_active', true)->inRandomOrder()->first();

        $order = Order::query()->create([
            'user_id' => $customer->id,
            'car_id' => $car->id,
            'car_unit_id' => $carUnit?->id,
            'price_type_id' => $priceType->id,
            'pickup_location_id' => $pickup->id,
            'dropoff_location_id' => $dropoff->id,
            'pickup_at' => $pickupAt,
            'dropoff_at' => $dropoffAt,
            'order_status' => $orderStatus,
            'rental_status' => $rentalStatus,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'customer_country' => 'AL',
            'base_rental_cents' => $baseRental,
            'extras_cents' => $extras,
            'fees_cents' => $fees,
            'discount_cents' => $discount,
            'tax_cents' => $tax,
            'total_cents' => $total,
            'currency' => 'EUR',
            'notes' => fake()->optional(0.3)->sentence(),
            'created_by_admin_id' => fake()->boolean(20) ? $admin?->id : null,
        ]);

        OrderLineItem::query()->create([
            'order_id' => $order->id,
            'kind' => 'rental',
            'label' => "{$car->name} ({$days} days)",
            'amount_cents' => $baseRental,
            'quantity' => 1,
            'sort_order' => 0,
        ]);

        if ($fees > 0) {
            OrderLineItem::query()->create([
                'order_id' => $order->id,
                'kind' => 'fee',
                'label' => 'One-way location fee',
                'amount_cents' => $fees,
                'quantity' => 1,
                'sort_order' => 1,
            ]);
        }

        if ($gpsOption && in_array($state, ['upcoming', 'active', 'completed'], true)) {
            $gpsTotal = $gpsOption->cost_cents * $days;
            $extras += $gpsTotal;
            OrderRentalOption::query()->create([
                'order_id' => $order->id,
                'rental_option_id' => $gpsOption->id,
                'quantity' => 1,
                'unit_price_cents' => $gpsOption->cost_cents,
                'total_cents' => $gpsTotal,
            ]);
        }

        if ($insuranceOption && in_array($state, ['active', 'completed'], true)) {
            $insTotal = $insuranceOption->cost_cents * $days;
            $extras += $insTotal;
            OrderRentalOption::query()->create([
                'order_id' => $order->id,
                'rental_option_id' => $insuranceOption->id,
                'quantity' => 1,
                'unit_price_cents' => $insuranceOption->cost_cents,
                'total_cents' => $insTotal,
            ]);
        }

        if ($extras > 0) {
            $order->update([
                'extras_cents' => $extras,
                'tax_cents' => (int) round(($baseRental + $extras + $fees) * 0.1),
                'total_cents' => $baseRental + $extras + $fees - $discount + (int) round(($baseRental + $extras + $fees) * 0.1),
            ]);
        }

        if (in_array($state, ['upcoming', 'active', 'completed'], true)) {
            OrderPayment::query()->create([
                'order_id' => $order->id,
                'amount_cents' => $order->total_cents,
                'method_code' => fake()->randomElement(['card', 'cash']),
                'status' => 'completed',
                'transaction_ref' => fake()->uuid(),
                'processed_at' => $order->created_at ?? now(),
            ]);
        }

        if ($state === 'pending') {
            OrderPayment::query()->create([
                'order_id' => $order->id,
                'amount_cents' => $order->total_cents,
                'method_code' => 'card',
                'status' => 'pending',
            ]);
        }

        if ($coupon && $state === 'completed' && fake()->boolean(40)) {
            $discountAmount = (int) round($order->total_cents * 0.1);
            $order->update([
                'coupon_id' => $coupon->id,
                'discount_cents' => $discountAmount,
                'total_cents' => max(0, $order->total_cents - $discountAmount),
            ]);

            CouponRedemption::query()->firstOrCreate(
                ['coupon_id' => $coupon->id, 'order_id' => $order->id],
                ['redeemed_at' => $order->dropoff_at]
            );
        }
    }
}
