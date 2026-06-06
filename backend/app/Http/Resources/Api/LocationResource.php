<?php

namespace App\Http\Resources\Api;

use App\Models\LocationFee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pickupFeeCents = LocationFee::query()
            ->where('pickup_location_id', $this->id)
            ->where('is_active', true)
            ->min('cost_cents');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'pickup_fee_cents' => $pickupFeeCents ? (int) $pickupFeeCents : 0,
        ];
    }
}
