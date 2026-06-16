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
            'sub_category_id' => $this->sub_category_id,
            'category_id' => $this->sub_category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'transmission' => $this->transmission,
            'fuel_type' => $this->fuel_type,
            'drive_type' => $this->drive_type instanceof \App\Enums\DriveType
                ? $this->drive_type->value
                : $this->drive_type,
            'seats' => $this->seats,
            'sleeps' => $this->sleeps,
            'bags' => $this->bags,
            'year' => $this->year,
            'main_image_path' => $this->main_image_path,
            'details_image_paths' => $this->details_image_paths ?? [],
            'og_image' => $this->og_image,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'units_available' => $this->units_available,
            'ical_import_url' => $this->ical_import_url,
            'pickup_time_from' => $this->formatTime($this->pickup_time_from),
            'pickup_time_to' => $this->formatTime($this->pickup_time_to),
            'dropoff_time_from' => $this->formatTime($this->dropoff_time_from),
            'dropoff_time_to' => $this->formatTime($this->dropoff_time_to),
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
            'rental_options' => $this->whenLoaded('rentalOptions', fn () => $this->rentalOptions->map(fn ($option) => [
                'id' => $option->id,
                'name' => $option->name,
                'slug' => $option->slug,
                'icon' => $option->icon,
                'description' => $option->description,
                'is_daily_cost' => (bool) ($option->pivot->is_daily_cost ?? $option->is_daily_cost),
                'cost_cents' => (int) ($option->pivot->cost_cents ?? $option->cost_cents),
                'default_cost_cents' => (int) $option->cost_cents,
                'cost_euros' => ((int) ($option->pivot->cost_cents ?? $option->cost_cents)) / 100,
            ])->values()->all()),
            'rental_condition_ids' => $this->whenLoaded('rentalConditions', fn () => $this->rentalConditions->pluck('id')->all()),
            'main_category_id' => $this->whenLoaded('subCategory', fn () => $this->subCategory?->main_category_id),
            'sub_category' => $this->whenLoaded('subCategory', fn () => [
                'id' => $this->subCategory->id,
                'name' => $this->subCategory->name,
                'slug' => $this->subCategory->slug,
                'main_category_id' => $this->subCategory->main_category_id,
            ]),
            'main_category' => $this->whenLoaded('subCategory.mainCategory', fn () => $this->subCategory?->mainCategory ? [
                'id' => $this->subCategory->mainCategory->id,
                'name' => $this->subCategory->mainCategory->name,
                'slug' => $this->subCategory->mainCategory->slug,
            ] : null),
            'category' => $this->whenLoaded('subCategory', fn () => [
                'id' => $this->subCategory->id,
                'name' => $this->subCategory->name,
                'slug' => $this->subCategory->slug,
            ]),
            'units_count' => $this->whenCounted('carUnits'),
            'location_fees' => HostLocationFeeResource::collection($this->whenLoaded('locationFees')),
            'out_of_hours_fees' => HostOutOfHoursFeeResource::collection($this->whenLoaded('outOfHoursFees')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    private function formatTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $str = (string) $value;
        if (preg_match('/^(\d{1,2}):(\d{2})/', $str, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return null;
    }
}
