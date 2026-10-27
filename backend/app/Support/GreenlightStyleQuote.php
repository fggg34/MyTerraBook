<?php

namespace App\Support;

use App\Models\Car;
use App\Models\DailyFare;
use App\Models\SpecialPrice;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Mirrors Greenlight calculateQuote / computeTripRentalRate against Terrabook rows.
 * Public checkout stays on RentalQuoteService. This class is for comparison only.
 */
final class GreenlightStyleQuote
{
    /**
     * @return array{
     *     days: int,
     *     base_daily_rate_cents: int,
     *     daily_rate_cents: int,
     *     base_rate_cents: int,
     *     extras_cents: int,
     *     addons_cents: int,
     *     one_way_fee_cents: int,
     *     out_of_hours_fee_cents: int,
     *     location_fee_cents: int,
     *     road_tax_cents: int,
     *     subtotal_cents: int,
     *     discount_cents: int,
     *     total_cents: int,
     *     applied_rule: array{id: int, name: string, adjustment_label: string}|null,
     *     rule_lines: list<array{key: string, label: string, amount_cents: int}>
     * }
     */
    public static function quote(Car $car, CarbonInterface $pickupAt, CarbonInterface $dropoffAt): array
    {
        $days = self::rentalDays($pickupAt, $dropoffAt);
        $baseDailyRateCents = self::baseDailyRateCents($car);
        $rate = self::computeTripRentalRate(
            $baseDailyRateCents,
            (int) $car->id,
            $pickupAt,
            $days,
            SpecialPrice::query()->where('is_active', true)->orderByDesc('created_at')->get(),
        );

        $baseRateCents = $rate['base_rate_cents'];

        return [
            'days' => $days,
            'base_daily_rate_cents' => $baseDailyRateCents,
            'daily_rate_cents' => $rate['daily_rate_cents'],
            'base_rate_cents' => $baseRateCents,
            'extras_cents' => 0,
            'addons_cents' => 0,
            'one_way_fee_cents' => 0,
            'out_of_hours_fee_cents' => 0,
            'location_fee_cents' => 0,
            'road_tax_cents' => 0,
            'subtotal_cents' => $baseRateCents,
            'discount_cents' => 0,
            'total_cents' => max(0, $baseRateCents),
            'applied_rule' => $rate['applied_rule'],
            'rule_lines' => $rate['rule_lines'],
        ];
    }

    public static function rentalDays(CarbonInterface $pickupAt, CarbonInterface $dropoffAt): int
    {
        $seconds = $dropoffAt->getTimestamp() - $pickupAt->getTimestamp();
        if ($seconds <= 0) {
            return 1;
        }

        return max(1, (int) ceil($seconds / 86400));
    }

    public static function baseDailyRateCents(Car $car): int
    {
        $standardId = DailyFarePricing::standardPriceTypeId();

        $query = DailyFare::query()->where('car_id', $car->id);
        if ($standardId !== null) {
            $withStandard = (clone $query)->where('price_type_id', $standardId)->orderBy('from_days')->get();
            $fares = $withStandard->isNotEmpty()
                ? $withStandard
                : $query->orderBy('from_days')->get();
        } else {
            $fares = $query->orderBy('from_days')->get();
        }

        $oneDay = $fares->first(function (DailyFare $fare): bool {
            return (int) $fare->from_days <= 1 && (int) $fare->to_days >= 1;
        });

        $fare = $oneDay ?? $fares->first();
        $cents = $fare !== null ? (int) $fare->price_per_day_cents : 0;
        if ($cents <= 0) {
            throw new InvalidArgumentException('No base daily rate on this car. Set a base rate on the vehicle before quoting.');
        }

        return $cents;
    }

    /**
     * @param  Collection<int, SpecialPrice>  $rules
     * @return array{
     *     daily_rate_cents: int,
     *     base_rate_cents: int,
     *     applied_rule: array{id: int, name: string, adjustment_label: string}|null,
     *     rule_lines: list<array{key: string, label: string, amount_cents: int}>
     * }
     */
    public static function computeTripRentalRate(
        int $baseDailyRateCents,
        int $carId,
        CarbonInterface $pickupAt,
        int $rentalDaysCount,
        Collection $rules,
    ): array {
        $baseDailyRateCents = max(0, (int) round($baseDailyRateCents));
        $days = max(1, (int) round($rentalDaysCount) ?: 1);

        $scopeRules = $rules->filter(function (SpecialPrice $rule) use ($carId): bool {
            return (bool) $rule->is_active && self::ruleMatchesVehicle($rule, $carId);
        });

        $byRule = [];
        $rentalTotalCents = 0;
        $startDay = $pickupAt->copy()->utc()->startOfDay();

        for ($i = 0; $i < $days; $i++) {
            $onDate = $startDay->copy()->addDays($i)->setTime(12, 0);
            $matching = $scopeRules->filter(fn (SpecialPrice $rule): bool => self::ruleMatchesDate($rule, $onDate));
            $winner = self::pickWinningRule($matching);

            if ($winner === null) {
                $rentalTotalCents += $baseDailyRateCents;

                continue;
            }

            $effectiveValue = self::resolveAdjustmentValueForDays(
                self::adjustmentValue($winner),
                self::parseDayOverrides($winner->day_overrides),
                $days,
            );
            $dayRateCents = self::applyAdjustment(
                $baseDailyRateCents,
                self::adjustmentType($winner),
                $effectiveValue,
            );
            $rentalTotalCents += $dayRateCents;

            $overrides = self::parseDayOverrides($winner->day_overrides);
            $matchedOverride = null;
            foreach ($overrides as $override) {
                if ($days >= $override['minDays']) {
                    $matchedOverride = $override;
                    break;
                }
            }
            $adjustmentLabel = self::formatAdjustmentLabel(self::adjustmentType($winner), $effectiveValue);
            if ($matchedOverride !== null) {
                $adjustmentLabel .= ' · '.$matchedOverride['minDays'].'+ days';
            }

            $id = (int) $winner->id;
            if (isset($byRule[$id])) {
                $byRule[$id]['dayCount']++;
                $byRule[$id]['deltaCents'] += $dayRateCents - $baseDailyRateCents;
            } else {
                $byRule[$id] = [
                    'rule' => $winner,
                    'dayCount' => 1,
                    'adjustmentLabel' => $adjustmentLabel,
                    'deltaCents' => $dayRateCents - $baseDailyRateCents,
                ];
            }
        }

        usort($byRule, fn (array $a, array $b): int => $b['dayCount'] <=> $a['dayCount']);

        $ruleLines = [];
        foreach ($byRule as $application) {
            if ($application['deltaCents'] === 0 && count($byRule) !== 1) {
                continue;
            }

            $rule = $application['rule'];
            $label = $application['deltaCents'] === 0
                ? 'Pricing rule: '.$rule->name.' ('.$application['adjustmentLabel'].', '.$application['dayCount'].'d, no change)'
                : 'Pricing rule: '.$rule->name.' ('.$application['adjustmentLabel'].', '.$application['dayCount'].'d)';

            $ruleLines[] = [
                'key' => 'pricing-rule-'.$rule->id,
                'label' => $label,
                'amount_cents' => $application['deltaCents'],
            ];
        }

        $primary = $byRule[0] ?? null;

        return [
            'daily_rate_cents' => (int) round($rentalTotalCents / $days),
            'base_rate_cents' => $rentalTotalCents,
            'rule_lines' => $ruleLines,
            'applied_rule' => $primary === null ? null : [
                'id' => (int) $primary['rule']->id,
                'name' => (string) $primary['rule']->name,
                'adjustment_label' => $primary['adjustmentLabel'],
            ],
        ];
    }

    public static function applyAdjustment(int $baseDailyRateCents, string $type, int $value): int
    {
        $base = max(0, (int) round($baseDailyRateCents));
        $v = (int) round($value);

        return match ($type) {
            'FIXED_PRICE' => max(0, $v),
            'PERCENT_INCREASE' => max(0, $base + self::percentOfCents($base, $v)),
            'PERCENT_DECREASE' => max(0, $base - self::percentOfCents($base, $v)),
            'FLAT_INCREASE' => max(0, $base + $v),
            'FLAT_DECREASE' => max(0, $base - $v),
            default => $base,
        };
    }

    public static function percentOfCents(int $amount, int $percent): int
    {
        return (int) round((max(0, (int) round($amount)) * $percent) / 100);
    }

    public static function pickWinningRule(Collection $rules): ?SpecialPrice
    {
        if ($rules->isEmpty()) {
            return null;
        }

        $sorted = $rules->all();
        usort($sorted, function (SpecialPrice $a, SpecialPrice $b): int {
            $rankDiff = self::specificityRank($b) - self::specificityRank($a);
            if ($rankDiff !== 0) {
                return $rankDiff;
            }

            return ($b->created_at?->getTimestamp() ?? 0) <=> ($a->created_at?->getTimestamp() ?? 0);
        });

        return $sorted[0];
    }

    /**
     * @return list<array{minDays: int, value: int}>
     */
    public static function parseDayOverrides(mixed $raw): array
    {
        if (! is_array($raw) || $raw === []) {
            return [];
        }

        $out = [];
        $isList = array_is_list($raw);
        foreach ($raw as $key => $item) {
            if ($isList && is_array($item)) {
                $minDays = (int) round((float) ($item['minDays'] ?? $item['min_days'] ?? 0));
                $value = (int) round((float) ($item['value'] ?? 0));
            } else {
                $minDays = (int) round((float) $key);
                $value = is_array($item)
                    ? (int) round((float) ($item['value'] ?? 0))
                    : (int) round((float) $item);
            }

            if ($minDays < 1 || $value < 0) {
                continue;
            }

            $out[] = ['minDays' => $minDays, 'value' => $value];
        }

        usort($out, fn (array $a, array $b): int => $b['minDays'] <=> $a['minDays']);

        return $out;
    }

    /**
     * @param  list<array{minDays: int, value: int}>  $overrides
     */
    public static function resolveAdjustmentValueForDays(int $baseValue, array $overrides, int $rentalDaysCount): int
    {
        $days = max(1, (int) round($rentalDaysCount) ?: 1);
        foreach ($overrides as $row) {
            if ($days >= $row['minDays']) {
                return $row['value'];
            }
        }

        return max(0, (int) round($baseValue));
    }

    private static function ruleMatchesVehicle(SpecialPrice $rule, int $carId): bool
    {
        $ids = self::vehicleIds($rule);
        if ($ids === []) {
            return true;
        }

        return in_array($carId, $ids, true);
    }

    private static function ruleMatchesDate(SpecialPrice $rule, CarbonInterface $onDate): bool
    {
        $day = $onDate->toDateString();
        if ($rule->date_from !== null && $day < $rule->date_from->toDateString()) {
            return false;
        }
        if ($rule->date_to !== null && $day > $rule->date_to->toDateString()) {
            return false;
        }

        return true;
    }

    private static function specificityRank(SpecialPrice $rule): int
    {
        return self::vehicleIds($rule) !== [] ? 4 : 1;
    }

    /**
     * @return list<int>
     */
    private static function vehicleIds(SpecialPrice $rule): array
    {
        $raw = $rule->vehicle_ids ?? [];
        if (! is_array($raw) || $raw === []) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $raw)));
    }

    private static function adjustmentType(SpecialPrice $rule): string
    {
        if ($rule->type === 'charge' && $rule->value_mode === 'percentage') {
            return 'PERCENT_INCREASE';
        }
        if ($rule->type === 'discount' && $rule->value_mode === 'percentage') {
            return 'PERCENT_DECREASE';
        }
        if ($rule->type === 'charge' && $rule->value_mode === 'fixed') {
            return 'FLAT_INCREASE';
        }
        if ($rule->type === 'discount' && $rule->value_mode === 'fixed') {
            return 'FLAT_DECREASE';
        }

        return 'PERCENT_INCREASE';
    }

    private static function adjustmentValue(SpecialPrice $rule): int
    {
        if ($rule->value_mode === 'percentage') {
            return (int) round(((int) ($rule->value_percent_bips ?? 0)) / 100);
        }

        return (int) ($rule->value_fixed_cents ?? 0);
    }

    private static function formatAdjustmentLabel(string $type, int $value): string
    {
        return match ($type) {
            'FIXED_PRICE' => $value.' cents/day fixed',
            'PERCENT_INCREASE' => '+'.$value.'%',
            'PERCENT_DECREASE' => '-'.$value.'%',
            'FLAT_INCREASE' => '+'.$value.' cents/day',
            'FLAT_DECREASE' => '-'.$value.' cents/day',
            default => (string) $value,
        };
    }
}
