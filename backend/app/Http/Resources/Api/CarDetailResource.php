<?php

namespace App\Http\Resources\Api;

use App\Models\Car;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarDetailResource extends JsonResource
{
    /**
     * @param  Car  $resource
     * @param  array<int, array{id:int,name:string,slug:string,attribute_label:?string,attribute_value_per_day:?string,from_price_per_day_cents:int}>  $priceTypes
     */
    public function __construct($resource, protected array $priceTypes = [])
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        $car = $this->resource;
        $pickupLocations = $car->relationLoaded('locations')
            ? $car->locations->filter(fn ($loc) => (bool) $loc->pivot->allows_pickup)->values()
            : $car->locations()->wherePivot('allows_pickup', true)->get();
        $dropoffLocations = $car->relationLoaded('locations')
            ? $car->locations->filter(fn ($loc) => (bool) $loc->pivot->allows_dropoff)->values()
            : $car->locations()->wherePivot('allows_dropoff', true)->get();

        return [
            'id' => $car->id,
            'name' => $car->name,
            'slug' => $car->slug,
            'meta_title' => $car->meta_title,
            'meta_description' => $car->meta_description,
            'og_image' => $car->og_image,
            'description' => $car->description,
            'sub_category' => SubCategoryResource::make($car->subCategory),
            'main_category' => MainCategoryResource::make($car->subCategory?->mainCategory),
            'category' => SubCategoryResource::make($car->subCategory),
            'transmission' => filled($car->transmission) ? $car->transmission : '—',
            'fuel_type' => filled($car->fuel_type) ? $car->fuel_type : '—',
            'seats' => $car->seats,
            'sleeps' => $car->sleeps,
            'bags' => $car->bags,
            'units_available' => $car->units_available,
            'host' => $car->host ? HostProfileResource::make($car->host) : null,
            'main_image_path' => $car->main_image_path,
            'details_image_paths' => $car->details_image_paths ?? [],
            'price_types' => collect($this->priceTypes)->map(fn (array $row) => [
                'id' => $row['id'],
                'name' => $row['name'],
                'slug' => $row['slug'],
                'attribute_label' => $row['attribute_label'] ?? null,
                'attribute_value_per_day' => $row['attribute_value_per_day'] ?? null,
                'from_price_per_day' => Money::formatDecimalFromCents($row['from_price_per_day_cents']),
                'from_price_per_day_cents' => $row['from_price_per_day_cents'],
            ]),
            'characteristics' => CharacteristicResource::collection($car->characteristics),
            'rental_options' => RentalOptionResource::collection($car->rentalOptions),
            'pickup_locations' => LocationResource::collection($pickupLocations),
            'dropoff_locations' => LocationResource::collection($dropoffLocations),
            'listing_reviews' => ListingReviewResource::collection(
                $car->relationLoaded('listingReviews') ? $car->listingReviews : collect(),
            ),
        ];
    }
}
