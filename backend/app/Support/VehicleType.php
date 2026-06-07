<?php

namespace App\Support;

use App\Models\SubCategory;

final class VehicleType
{
    public static function fromSubCategory(?SubCategory $subCategory): string
    {
        $slug = $subCategory?->mainCategory?->slug;

        if ($slug === 'campervan') {
            return 'campervan';
        }

        return 'car';
    }

    public static function fromMainCategorySlug(?string $slug): string
    {
        if ($slug === 'campervan') {
            return 'campervan';
        }

        return 'car';
    }

    /** @deprecated Use fromSubCategory() or fromMainCategorySlug() */
    public static function fromCategoryName(?string $categoryName): string
    {
        return 'car';
    }
}
