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
            'category_id' => $this->resource['category_id'],
            'transmission' => $this->resource['transmission'] ?? '—',
            'fuel_type' => $this->resource['fuel_type'] ?? '—',
            'base_daily_price' => Money::formatDecimalFromCents((int) $minCents),
            'base_daily_price_cents' => (int) $minCents,
            'thumbnail_url' => $this->resource['main_image_path'],
            'units_available' => (int) $this->resource['units_available'],
        ];
    }
}
