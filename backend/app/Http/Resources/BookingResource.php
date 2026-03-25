<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'status' => $this->status,
            'pickup_at' => $this->pickup_at,
            'dropoff_at' => $this->dropoff_at,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'rental_subtotal' => $this->rental_subtotal,
            'extras_subtotal' => $this->extras_subtotal,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'currency' => $this->currency,
            'pricing_snapshot' => $this->pricing_snapshot,
            'car' => CarResource::make($this->whenLoaded('car')),
            'pickup_location' => LocationResource::make($this->whenLoaded('pickupLocation')),
            'dropoff_location' => LocationResource::make($this->whenLoaded('dropoffLocation')),
            'coupon' => CouponResource::make($this->whenLoaded('coupon')),
            'extras' => $this->whenLoaded('extras'),
        ];
    }
}
