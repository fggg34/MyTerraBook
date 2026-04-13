<?php

namespace App\Http\Resources\Api;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'order_status' => $this->order_status->value,
            'rental_status' => $this->rental_status?->value,
            'pickup_at' => $this->pickup_at->toIso8601String(),
            'dropoff_at' => $this->dropoff_at->toIso8601String(),
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'base_rental' => Money::formatDecimalFromCents((int) $this->base_rental_cents),
            'extras' => Money::formatDecimalFromCents((int) $this->extras_cents),
            'fees' => Money::formatDecimalFromCents((int) $this->fees_cents),
            'discount' => Money::formatDecimalFromCents((int) $this->discount_cents),
            'tax' => Money::formatDecimalFromCents((int) $this->tax_cents),
            'total' => Money::formatDecimalFromCents((int) $this->total_cents),
            'currency' => $this->currency,
            'car' => $this->when(
                $this->relationLoaded('car') && $this->car,
                fn () => [
                    'id' => $this->car->id,
                    'name' => $this->car->name,
                    'slug' => $this->car->slug,
                ]
            ),
        ];
    }
}
