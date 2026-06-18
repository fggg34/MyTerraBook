<?php

namespace App\Support;

use App\Models\Car;
use App\Models\DailyFare;
use Carbon\Carbon;

final class HostPricingValidation
{
    public const MAX_DISCOUNT_PERCENT_BIPS = 10000;

    public const MAX_SURCHARGE_PERCENT_BIPS = 20000;

    public static function dayRangesOverlap(int $aFrom, int $aTo, int $bFrom, int $bTo): bool
    {
        return $aFrom <= $bTo && $bFrom <= $aTo;
    }

    public static function isBaseDailyFare(int $fromDays, int $toDays): bool
    {
        return $fromDays === 1 && $toDays >= 365;
    }

    public static function assertDailyFareRules(Car $car, array $data, ?DailyFare $exclude = null): void
    {
        $priceCents = (int) ($data['price_per_day_cents'] ?? 0);
        if ($priceCents <= 0) {
            abort(422, 'Enter a daily rate greater than zero.');
        }

        $standardId = DailyFarePricing::standardPriceTypeId();
        $priceTypeId = (int) ($data['price_type_id'] ?? 0);
        $fromDays = (int) ($data['from_days'] ?? 0);
        $toDays = (int) ($data['to_days'] ?? 0);

        if ($standardId === null || $priceTypeId !== $standardId) {
            return;
        }

        if (self::isBaseDailyFare($fromDays, $toDays)) {
            return;
        }

        $baseFare = $car->dailyFares()
            ->where('price_type_id', $standardId)
            ->where('from_days', 1)
            ->where('to_days', '>=', 365)
            ->first();

        if ($baseFare && $priceCents >= (int) $baseFare->price_per_day_cents) {
            abort(422, 'Duration tiers must be cheaper than your standard daily rate.');
        }

        $tiers = $car->dailyFares()
            ->where('price_type_id', $standardId)
            ->get()
            ->filter(fn (DailyFare $fare) => ! self::isBaseDailyFare((int) $fare->from_days, (int) $fare->to_days))
            ->when($exclude !== null, fn ($collection) => $collection->where('id', '!=', $exclude->id));

        foreach ($tiers as $tier) {
            if (self::dayRangesOverlap($fromDays, $toDays, (int) $tier->from_days, (int) $tier->to_days)) {
                abort(422, sprintf(
                    'This range overlaps with an existing tier (days %d–%d). Adjust the dates or remove the existing tier first.',
                    $tier->from_days,
                    $tier->to_days,
                ));
            }
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function assertSpecialPriceRules(array $data): void
    {
        $type = (string) ($data['type'] ?? '');
        $valueMode = (string) ($data['value_mode'] ?? '');

        if (! in_array($type, ['charge', 'discount'], true)) {
            abort(422, 'Type must be surcharge or discount.');
        }

        if (! in_array($valueMode, ['percentage', 'fixed'], true)) {
            abort(422, 'Adjustment must be percentage or fixed amount.');
        }

        if ($valueMode === 'percentage') {
            $bips = (int) ($data['value_percent_bips'] ?? 0);
            if ($bips <= 0) {
                abort(422, 'Enter a percentage greater than zero.');
            }
            if ($type === 'discount' && $bips > self::MAX_DISCOUNT_PERCENT_BIPS) {
                abort(422, 'Discounts cannot exceed 100%.');
            }
            if ($type === 'charge' && $bips > self::MAX_SURCHARGE_PERCENT_BIPS) {
                abort(422, 'Surcharge seems unusually high (max 200%). Lower it or contact support.');
            }

            return;
        }

        $fixedCents = (int) ($data['value_fixed_cents'] ?? 0);
        if ($fixedCents <= 0) {
            abort(422, 'Enter an amount greater than zero.');
        }
    }

    public static function assertNotPastDate(string $field, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $date = Carbon::parse($value)->startOfDay();
        if ($date->lt(now()->startOfDay())) {
            abort(422, 'Cannot block dates in the past.');
        }
    }

    public static function dateRangesOverlap(string $aFrom, string $aTo, string $bFrom, string $bTo): bool
    {
        $aStart = Carbon::parse($aFrom)->startOfDay();
        $aEnd = Carbon::parse($aTo)->endOfDay();
        $bStart = Carbon::parse($bFrom)->startOfDay();
        $bEnd = Carbon::parse($bTo)->endOfDay();

        return $aStart->lte($bEnd) && $bStart->lte($aEnd);
    }
}
