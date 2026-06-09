<?php

namespace App\Http\Resources\Api;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $minCents = $this->resource['min_daily_price_cents'] ?? 0;

        return [
            'id' => $this->resource['id'],
            'name' => $this->resource['name'],
            'slug' => $this->resource['slug'],
            'sub_category_id' => $this->resource['sub_category_id'] ?? null,
            'sub_category_name' => $this->resource['sub_category_name'] ?? null,
            'main_category_slug' => $this->resource['main_category_slug'] ?? null,
            'main_category_name' => $this->resource['main_category_name'] ?? null,
            'category_id' => $this->resource['category_id'] ?? $this->resource['sub_category_id'] ?? null,
            'category_name' => $this->resource['category_name'] ?? $this->resource['sub_category_name'] ?? null,
            'transmission' => $this->resource['transmission'] ?? '—',
            'fuel_type' => $this->resource['fuel_type'] ?? '—',
            'seats' => $this->resource['seats'] ?? null,
            'sleeps' => $this->resource['sleeps'] ?? null,
            'bags' => $this->resource['bags'] ?? null,
            'base_daily_price' => Money::formatDecimalFromCents((int) $minCents),
            'base_daily_price_cents' => (int) $minCents,
            'thumbnail_url' => $this->resource['main_image_path'],
            'units_available' => (int) $this->resource['units_available'],
            'search_pricing' => $this->resource['search_pricing'] ?? null,
        ];
    }
}
