<?php

namespace App\Http\Resources\Host;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostCarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'transmission' => $this->transmission,
            'fuel_type' => $this->fuel_type,
            'main_image_path' => $this->main_image_path,
            'details_image_paths' => $this->details_image_paths ?? [],
            'og_image' => $this->og_image,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'units_available' => $this->units_available,
            'ical_import_url' => $this->ical_import_url,
            'is_active' => $this->is_active,
            'listing_status' => $this->listing_status?->value,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'location_ids' => $this->whenLoaded('locations', fn () => $this->locations->pluck('id')->all()),
            'pickup_location_ids' => $this->whenLoaded('locations', fn () => $this->locations->filter(fn ($loc) => (bool) $loc->pivot->allows_pickup)->pluck('id')->values()->all()),
            'dropoff_location_ids' => $this->whenLoaded('locations', fn () => $this->locations->filter(fn ($loc) => (bool) $loc->pivot->allows_dropoff)->pluck('id')->values()->all()),
            'characteristic_ids' => $this->whenLoaded('characteristics', fn () => $this->characteristics->pluck('id')->all()),
            'rental_option_ids' => $this->whenLoaded('rentalOptions', fn () => $this->rentalOptions->pluck('id')->all()),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'units_count' => $this->whenCounted('carUnits'),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
