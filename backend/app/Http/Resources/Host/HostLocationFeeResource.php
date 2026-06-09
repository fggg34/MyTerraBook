<?php

namespace App\Http\Resources\Host;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HostLocationFeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'car_id' => $this->car_id,
            'pickup_location_id' => $this->pickup_location_id,
            'dropoff_location_id' => $this->dropoff_location_id,
            'pickup_location' => $this->whenLoaded('pickupLocation', fn () => [
                'id' => $this->pickupLocation->id,
                'name' => $this->pickupLocation->name,
            ]),
            'dropoff_location' => $this->whenLoaded('dropoffLocation', fn () => [
                'id' => $this->dropoffLocation->id,
                'name' => $this->dropoffLocation->name,
            ]),
            'cost_cents' => $this->cost_cents,
            'cost_euros' => round($this->cost_cents / 100, 2),
            'multiply_by_days' => $this->multiply_by_days,
            'is_one_way_fee' => $this->is_one_way_fee,
            'is_active' => $this->is_active,
        ];
    }
}
