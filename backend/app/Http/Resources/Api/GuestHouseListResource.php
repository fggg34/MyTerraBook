<?php

namespace App\Http\Resources\Api;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GuestHouse */
class GuestHouseListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $avgRating = $this->reviews()
            ->where('is_approved', true)
            ->avg('rating');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'short_description' => $this->short_description,
            'city' => $this->city,
            'country' => $this->country,
            'max_guests' => $this->max_guests,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'thumbnail' => $this->thumbnail,
            'base_price_per_night_cents' => $this->base_price_per_night,
            'base_price_per_night_formatted' => '€ '.Money::formatDecimalFromCents($this->base_price_per_night),
            'rating' => $avgRating ? round((float) $avgRating, 1) : null,
            'review_count' => $this->reviews()->where('is_approved', true)->count(),
        ];
    }
}
