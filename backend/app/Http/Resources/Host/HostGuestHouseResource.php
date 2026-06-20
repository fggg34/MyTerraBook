<?php

namespace App\Http\Resources\Host;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostGuestHouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'type' => $this->type?->value,
            'status' => $this->status?->value,
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
            'base_price_per_night' => $this->base_price_per_night,
            'base_price_per_night_euros' => $this->centsToEuros($this->base_price_per_night),
            'cleaning_fee' => $this->cleaning_fee,
            'cleaning_fee_euros' => $this->centsToEuros($this->cleaning_fee),
            'security_deposit' => $this->security_deposit,
            'security_deposit_euros' => $this->centsToEuros($this->security_deposit),
            'check_in_time' => $this->formatTime($this->check_in_time),
            'check_out_time' => $this->formatTime($this->check_out_time),
            'cancellation_policy' => $this->cancellation_policy?->value,
            'thumbnail' => $this->thumbnail,
            'og_image' => $this->og_image,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'tax_rate_id' => $this->tax_rate_id,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'amenity_ids' => $this->whenLoaded('amenities', fn () => $this->amenities->pluck('id')->all()),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'path' => $img->path,
                'caption' => $img->caption,
                'sort_order' => $img->sort_order,
            ])),
            'room_details' => $this->whenLoaded('roomDetails', fn () => $this->roomDetails->map(fn ($detail) => [
                'id' => $detail->id,
                'title' => $detail->title,
                'text' => $detail->text,
                'dim' => $detail->dim,
                'image_path' => $detail->image_path,
                'sort_order' => $detail->sort_order,
            ])),
            'seasonal_prices' => $this->whenLoaded('seasonalPrices', fn () => $this->seasonalPrices->map(fn ($sp) => [
                'id' => $sp->id,
                'name' => $sp->name,
                'date_from' => $sp->date_from?->toDateString(),
                'date_to' => $sp->date_to?->toDateString(),
                'price_per_night' => $sp->price_per_night,
                'price_per_night_euros' => $this->centsToEuros($sp->price_per_night),
                'minimum_nights' => $sp->minimum_nights,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function centsToEuros(mixed $cents): ?float
    {
        if ($cents === null || $cents === '') {
            return null;
        }

        return round(((int) $cents) / 100, 2);
    }

    private function formatTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $str = (string) $value;
        if (preg_match('/^(\d{1,2}):(\d{2})/', $str, $matches)) {
            return sprintf('%02d:%02d', (int) $matches[1], (int) $matches[2]);
        }

        return null;
    }
}
