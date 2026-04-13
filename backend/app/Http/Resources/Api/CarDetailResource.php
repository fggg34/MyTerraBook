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

        return [
            'id' => $car->id,
            'name' => $car->name,
            'slug' => $car->slug,
            'description' => $car->description,
            'category' => CategoryResource::make($car->category),
            'transmission' => '—',
            'fuel_type' => '—',
            'units_available' => $car->units_available,
            'main_image_path' => $car->main_image_path,
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
        ];
    }
}
