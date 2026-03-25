<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Coupon;
use App\Models\Extra;
use App\Models\PricingRule;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PricingService
{
    public function quote(
        Car $car,
        Carbon $pickupAt,
        Carbon $dropoffAt,
        int $pickupLocationId,
        array $extras = [],
        ?Coupon $coupon = null
    ): array {
        $duration = app(RentalDurationService::class)->calculate($pickupAt, $dropoffAt);
        $unit = $this->resolveTimeUnit($car, $duration['hours']);
        $units = $unit === 'hour' ? $duration['hours'] : $duration['days'];
        $baseUnitPrice = $unit === 'hour'
            ? (float) ($car->base_hourly_price ?: ($car->base_daily_price / 24))
            : (float) $car->base_daily_price;

        $adjustedUnitPrice = $this->applyRules(
            baseUnitPrice: $baseUnitPrice,
            carId: $car->id,
            locationId: $pickupLocationId,
            pickupAt: $pickupAt,
            dropoffAt: $dropoffAt,
            timeUnit: $unit,
        );

        $rentalSubtotal = $adjustedUnitPrice * $units;
        $extrasSubtotal = $this->calculateExtrasSubtotal($extras, $units, $unit);

        $subtotal = $rentalSubtotal + $extrasSubtotal;
        $discount = $this->calculateDiscount($coupon, $subtotal);
        $total = max(0, $subtotal - $discount);

        return [
            'duration' => $duration,
            'pricing_mode' => $unit,
            'unit_price' => round($adjustedUnitPrice, 2),
            'rental_subtotal' => round($rentalSubtotal, 2),
            'extras_subtotal' => round($extrasSubtotal, 2),
            'discount_amount' => round($discount, 2),
            'tax_amount' => 0.0,
            'total' => round($total, 2),
        ];
    }

    private function resolveTimeUnit(Car $car, int $hours): string
    {
        if ($car->base_hourly_price !== null && $hours < 24) {
            return 'hour';
        }

        return 'day';
    }

    private function applyRules(
        float $baseUnitPrice,
        int $carId,
        int $locationId,
        Carbon $pickupAt,
        Carbon $dropoffAt,
        string $timeUnit,
    ): float {
        $rules = PricingRule::query()
            ->where('is_active', true)
            ->where('time_unit', $timeUnit)
            ->where(fn ($query) => $query->whereNull('car_id')->orWhere('car_id', $carId))
            ->where(fn ($query) => $query->whereNull('location_id')->orWhere('location_id', $locationId))
            ->where(fn ($query) => $query
                ->whereNull('date_from')
                ->orWhereDate('date_from', '<=', $dropoffAt->toDateString()))
            ->where(fn ($query) => $query
                ->whereNull('date_to')
                ->orWhereDate('date_to', '>=', $pickupAt->toDateString()))
            ->orderByDesc('priority')
            ->get();

        return $rules->reduce(function (float $price, PricingRule $rule): float {
            return match ($rule->adjustment) {
                'set' => (float) $rule->amount,
                'multiply' => $price * (float) $rule->amount,
                'add' => $price + (float) $rule->amount,
                default => $price,
            };
        }, $baseUnitPrice);
    }

    private function calculateExtrasSubtotal(array $extras, int $units, string $timeUnit): float
    {
        if (empty($extras)) {
            return 0.0;
        }

        /** @var Collection<int, Extra> $extrasModels */
        $extrasModels = Extra::query()->whereIn('id', array_keys($extras))->get()->keyBy('id');

        return collect($extras)->reduce(function (float $carry, int $quantity, int $extraId) use ($extrasModels, $units, $timeUnit): float {
            $extra = $extrasModels->get($extraId);
            if (! $extra) {
                return $carry;
            }

            $qty = max(1, $quantity);
            $unitPrice = (float) $extra->unit_price;

            $lineTotal = match ($extra->price_type) {
                'fixed' => $unitPrice * $qty,
                'per_hour' => $timeUnit === 'hour' ? $unitPrice * $units * $qty : $unitPrice * 24 * $units * $qty,
                default => $unitPrice * $units * $qty,
            };

            return $carry + $lineTotal;
        }, 0.0);
    }

    private function calculateDiscount(?Coupon $coupon, float $subtotal): float
    {
        if (! $coupon || ! $coupon->is_active) {
            return 0.0;
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return 0.0;
        }

        if ($coupon->usage_limit !== null && $coupon->times_used >= $coupon->usage_limit) {
            return 0.0;
        }

        if ($coupon->min_order_amount !== null && $subtotal < (float) $coupon->min_order_amount) {
            return 0.0;
        }

        if ($coupon->discount_type === 'percentage') {
            return ($subtotal * (float) $coupon->discount_value) / 100;
        }

        return min($subtotal, (float) $coupon->discount_value);
    }
}
