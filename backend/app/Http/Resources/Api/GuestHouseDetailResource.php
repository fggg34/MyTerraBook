<?php

namespace App\Http\Resources\Api;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GuestHouse */
class GuestHouseDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approvedReviews = $this->relationLoaded('listingReviews')
            ? $this->listingReviews
            : $this->listingReviews()->approved()->get();

        $avgRating = $approvedReviews->avg('rating');

        $amenities = $this->amenities->groupBy('group')->map(
            fn ($items, $group) => [
                'group' => $group ?: 'general',
                'items' => $items->map(fn ($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'icon' => $a->icon,
                ])->values(),
            ],
        )->values();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'og_image' => $this->og_image,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'max_guests' => $this->max_guests,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'beds' => $this->beds,
            'min_nights' => $this->min_nights,
            'max_nights' => $this->max_nights,
            'base_price_per_night_cents' => $this->base_price_per_night,
            'base_price_per_night_formatted' => '€ '.Money::formatDecimalFromCents($this->base_price_per_night),
            'cleaning_fee_cents' => $this->cleaning_fee,
            'security_deposit_cents' => $this->security_deposit,
            'check_in_time' => $this->check_in_time,
            'check_out_time' => $this->check_out_time,
            'cancellation_policy' => $this->cancellation_policy->value,
            'thumbnail' => $this->thumbnail,
            'images' => $this->images->map(fn ($img) => [
                'path' => $img->path,
                'caption' => $img->caption,
                'sort_order' => $img->sort_order,
            ]),
            'amenities' => $amenities,
            'seasonal_prices' => $this->seasonalPrices->map(fn ($sp) => [
                'name' => $sp->name,
                'date_from' => $sp->date_from->toDateString(),
                'date_to' => $sp->date_to->toDateString(),
                'price_per_night_cents' => $sp->price_per_night,
                'minimum_nights' => $sp->minimum_nights,
            ]),
            'rating' => $avgRating ? round((float) $avgRating, 1) : null,
            'listing_reviews' => ListingReviewResource::collection($approvedReviews),
        ];
    }
}
