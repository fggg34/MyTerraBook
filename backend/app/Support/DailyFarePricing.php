<?php

namespace App\Support;

use App\Models\DailyFare;
use App\Models\PriceType;
use Illuminate\Database\Eloquent\Builder;

final class DailyFarePricing
{
    public const STANDARD_PRICE_TYPE_SLUG = 'basic';

    public static function standardPriceTypeId(): ?int
    {
        $id = PriceType::query()
            ->where('slug', self::STANDARD_PRICE_TYPE_SLUG)
            ->where('is_active', true)
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * Cheapest configured daily rate per rate plan for a car.
     * This powers the "From" teaser, so it intentionally returns the
     * lowest tier (e.g. a long-duration discount) rather than the base fare.
     *
     * @return array<int, int> price_type_id => cheapest price_per_day_cents
     */
    public static function fromPriceCentsByPriceTypeForCar(int $carId): array
    {
        return DailyFare::query()
            ->where('car_id', $carId)
            ->selectRaw('price_type_id, MIN(price_per_day_cents) as min_cents')
            ->groupBy('price_type_id')
            ->pluck('min_cents', 'price_type_id')
            ->map(fn ($cents) => (int) $cents)
            ->all();
    }

    /**
     * Subquery yielding the cheapest daily rate per car for list/search cards.
     * Restricted to the standard rate plan when one exists so protection
     * upgrade rate plans don't skew the "from" figure.
     */
    public static function cheapestFareListSubquery(): Builder
    {
        $standardId = self::standardPriceTypeId();

        return DailyFare::query()
            ->select('car_id')
            ->selectRaw('MIN(price_per_day_cents) as min_daily_price_cents')
            ->when($standardId !== null, fn (Builder $q) => $q->where('price_type_id', $standardId))
            ->groupBy('car_id');
    }
}
