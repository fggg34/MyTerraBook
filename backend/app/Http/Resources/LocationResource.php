<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'allows_pickup' => $this->allows_pickup,
            'allows_dropoff' => $this->allows_dropoff,
            'opening_hours' => $this->opening_hours,
            'closed_days' => $this->closed_days,
        ];
    }
}
