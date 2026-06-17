<?php

namespace App\Support;

final class RentalOptionPricing
{
    public static function resolveIsDailyCost(?bool $pivotValue, bool $catalogDefault): bool
    {
        return $pivotValue ?? $catalogDefault;
    }

    public static function resolveCostCents(?int $pivotCents, int $catalogCents): int
    {
        return $pivotCents ?? $catalogCents;
    }

    public static function lineTotalCents(
        int $unitCents,
        bool $isDailyCost,
        int $quantity,
        int $rentalDays,
        ?int $maxCapCents = null,
    ): int {
        $line = $isDailyCost
            ? max(1, $quantity) * $unitCents * $rentalDays
            : max(1, $quantity) * $unitCents;

        if ($maxCapCents !== null) {
            $line = min($line, $maxCapCents);
        }

        return $line;
    }
}
