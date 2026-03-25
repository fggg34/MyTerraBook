<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
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
            'seats' => $this->seats,
            'bags' => $this->bags,
            'features' => $this->features,
            'availability_status' => $this->availability_status,
            'base_daily_price' => $this->base_daily_price,
            'base_hourly_price' => $this->base_hourly_price,
            'thumbnail_path' => $this->thumbnail_path,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'images' => $this->whenLoaded('images'),
        ];
    }
}
