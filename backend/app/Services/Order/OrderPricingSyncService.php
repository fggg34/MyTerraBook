<?php

namespace App\Services\Order;

use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\OrderRentalOption;
use Carbon\Carbon;

class OrderPricingSyncService
{
    /**
     * @param  array<string, mixed>  $quote
     */
    public function applyQuoteToOrder(Order $order, array $quote, array $changes = []): Order
    {
        if (isset($changes['pickup_at'])) {
            $order->pickup_at = Carbon::parse($changes['pickup_at']);
        }
        if (isset($changes['dropoff_at'])) {
            $order->dropoff_at = Carbon::parse($changes['dropoff_at']);
        }
        if (isset($changes['pickup_location_id'])) {
            $order->pickup_location_id = (int) $changes['pickup_location_id'];
        }
        if (isset($changes['dropoff_location_id'])) {
            $order->dropoff_location_id = (int) $changes['dropoff_location_id'];
        }
        if (isset($changes['price_type_id'])) {
            $order->price_type_id = (int) $changes['price_type_id'];
        }

        $order->base_rental_cents = (int) $quote['base_rental_cents'];
        $order->extras_cents = (int) $quote['extras_cents'];
        $order->fees_cents = (int) $quote['fees_cents'];
        $order->discount_cents = (int) $quote['discount_cents'];
        $order->tax_cents = (int) $quote['tax_cents'];
        $order->total_cents = (int) $quote['total_cents'];
        $order->currency = $quote['currency'];
        $order->coupon_id = $quote['coupon_id'] ?? null;
        $order->pricing_snapshot = $quote;
        $order->save();

        $order->lineItems()->delete();
        $order->rentalOptions()->delete();

        OrderLineItem::query()->create([
            'order_id' => $order->id,
            'kind' => 'base_rental',
            'label' => 'Vehicle rental',
            'amount_cents' => $quote['base_rental_cents'],
            'sort_order' => 0,
        ]);

        foreach ($quote['extras_lines'] as $i => $line) {
            OrderLineItem::query()->create([
                'order_id' => $order->id,
                'kind' => 'rental_option',
                'label' => $line['name'],
                'amount_cents' => $line['total_cents'],
                'quantity' => $line['quantity'],
                'sort_order' => $i + 1,
            ]);

            OrderRentalOption::query()->create([
                'order_id' => $order->id,
                'rental_option_id' => $line['rental_option_id'],
                'quantity' => $line['quantity'],
                'unit_price_cents' => (int) $line['unit_price_cents'],
                'total_cents' => $line['total_cents'],
            ]);
        }

        foreach ($quote['fees_lines'] as $j => $feeLine) {
            OrderLineItem::query()->create([
                'order_id' => $order->id,
                'kind' => 'fee',
                'label' => $feeLine['label'],
                'amount_cents' => $feeLine['amount_cents'],
                'sort_order' => 50 + $j,
            ]);
        }

        if ($quote['discount_cents'] > 0) {
            OrderLineItem::query()->create([
                'order_id' => $order->id,
                'kind' => 'discount',
                'label' => 'Discount',
                'amount_cents' => -$quote['discount_cents'],
                'sort_order' => 90,
            ]);
        }

        if ($quote['tax_cents'] > 0) {
            OrderLineItem::query()->create([
                'order_id' => $order->id,
                'kind' => 'tax',
                'label' => 'Tax',
                'amount_cents' => $quote['tax_cents'],
                'sort_order' => 100,
            ]);
        }

        if ($quote['coupon_id'] !== null) {
            $coupon = Coupon::query()->find($quote['coupon_id']);
            if ($coupon && $coupon->type === 'gift') {
                CouponRedemption::query()->firstOrCreate([
                    'coupon_id' => $quote['coupon_id'],
                    'order_id' => $order->id,
                ]);
            }
        }

        return $order->fresh();
    }

    public function pricingSnapshotForOrder(Order $order): array
    {
        return [
            'total_cents' => (int) $order->total_cents,
            'currency' => $order->currency,
            'pickup_at' => $order->pickup_at?->toIso8601String(),
            'dropoff_at' => $order->dropoff_at?->toIso8601String(),
        ];
    }
}
