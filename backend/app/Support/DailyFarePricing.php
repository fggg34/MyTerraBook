<?php

namespace App\Support;

use App\Models\DailyFare;
use App\Models\PriceType;
use Illuminate\Database\Eloquent\Builder;

final class DailyFarePricing
{
    public const BASE_FROM_DAYS = 1;

    public const BASE_TO_DAYS_MIN = 365;

    public const STANDARD_PRICE_TYPE_SLUG = 'basic';

    public static function standardPriceTypeId(): ?int
    {
        $id = PriceType::query()
            ->where('slug', self::STANDARD_PRICE_TYPE_SLUG)
            ->where('is_active', true)
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    public static function baseFareScope(Builder $query, ?int $priceTypeId = null): Builder
    {
        if ($priceTypeId !== null) {
            $query->where('price_type_id', $priceTypeId);
        }

        return $query
            ->where('from_days', self::BASE_FROM_DAYS)
            ->where('to_days', '>=', self::BASE_TO_DAYS_MIN);
    }

    public static function baseFareCentsForCar(int $carId, ?int $priceTypeId = null): ?int
    {
        $priceTypeId ??= self::standardPriceTypeId();
        if ($priceTypeId === null) {
            return null;
        }

        $cents = self::baseFareScope(DailyFare::query(), $priceTypeId)
            ->where('car_id', $carId)
            ->value('price_per_day_cents');

        if ($cents !== null) {
            return (int) $cents;
        }

        return DailyFare::query()
            ->where('car_id', $carId)
            ->where('price_type_id', $priceTypeId)
            ->where('from_days', '<=', self::BASE_FROM_DAYS)
            ->where('to_days', '>=', self::BASE_FROM_DAYS)
            ->orderBy('from_days', 'desc')
            ->value('price_per_day_cents');
    }

    /** @return array<int, int> price_type_id => from_price_per_day_cents */
    public static function fromPriceCentsByPriceTypeForCar(int $carId): array
    {
        $baseFares = DailyFare::query()
            ->where('car_id', $carId)
            ->where('from_days', self::BASE_FROM_DAYS)
            ->where('to_days', '>=', self::BASE_TO_DAYS_MIN)
            ->pluck('price_per_day_cents', 'price_type_id');

        $priceTypeIds = DailyFare::query()
            ->where('car_id', $carId)
            ->distinct()
            ->pluck('price_type_id');

        $result = [];
        foreach ($priceTypeIds as $priceTypeId) {
            $priceTypeId = (int) $priceTypeId;
            if ($baseFares->has($priceTypeId)) {
                $result[$priceTypeId] = (int) $baseFares->get($priceTypeId);

                continue;
            }

            $fallback = DailyFare::query()
                ->where('car_id', $carId)
                ->where('price_type_id', $priceTypeId)
                ->where('from_days', '<=', self::BASE_FROM_DAYS)
                ->where('to_days', '>=', self::BASE_FROM_DAYS)
                ->orderBy('from_days', 'desc')
                ->value('price_per_day_cents');

            if ($fallback !== null) {
                $result[$priceTypeId] = (int) $fallback;
            }
        }

        return $result;
    }

    public static function baseFareListSubquery(): Builder
    {
        $standardId = self::standardPriceTypeId();

        return DailyFare::query()
            ->select('car_id')
            ->selectRaw('price_per_day_cents as min_daily_price_cents')
            ->when($standardId !== null, fn (Builder $q) => $q->where('price_type_id', $standardId))
            ->where('from_days', self::BASE_FROM_DAYS)
            ->where('to_days', '>=', self::BASE_TO_DAYS_MIN);
    }
}
