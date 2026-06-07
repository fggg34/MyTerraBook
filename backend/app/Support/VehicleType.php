<?php

namespace App\Support;

final class VehicleType
{
    /** @var list<string> */
    private const CAMPERVAN_CATEGORIES = ['Van', 'SUV'];

    public static function fromCategoryName(?string $categoryName): string
    {
        if ($categoryName !== null && in_array($categoryName, self::CAMPERVAN_CATEGORIES, true)) {
            return 'campervan';
        }

        return 'car';
    }
}
