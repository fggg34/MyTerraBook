<?php

namespace App\Http\Resources\Api\Host;

use App\Http\Resources\Api\BookingChangeRequestResource;
use App\Support\Money;
use App\Support\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Order */
class HostOrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kind' => 'car',
            'id' => $this->id,
            'reference' => $this->reference,
            'confirmation_url' => $this->confirmation_url,
            'order_status' => $this->order_status->value,
            'rental_status' => $this->rental_status?->value,
            'pickup_at' => $this->pickup_at->toIso8601String(),
            'dropoff_at' => $this->dropoff_at->toIso8601String(),
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'customer_country' => $this->customer_country,
            'notes' => $this->notes,
            'custom_field_values' => $this->custom_field_values,
            'currency' => $this->currency,
            'total_cents' => (int) $this->total_cents,
            'total_formatted' => Money::formatDecimalFromCents((int) $this->total_cents).' '.$this->currency,
            'base_rental_cents' => (int) $this->base_rental_cents,
            'extras_cents' => (int) $this->extras_cents,
            'fees_cents' => (int) $this->fees_cents,
            'discount_cents' => (int) $this->discount_cents,
            'tax_cents' => (int) $this->tax_cents,
            'created_at' => $this->created_at?->toIso8601String(),
            'car' => $this->whenLoaded('car', fn () => $this->car ? [
                'id' => $this->car->id,
                'name' => $this->car->name,
                'slug' => $this->car->slug,
                'thumbnail' => $this->car->main_image_path,
                'vehicle_type' => VehicleType::fromSubCategory(
                    $this->car->relationLoaded('subCategory') ? $this->car->subCategory : null
                ),
            ] : null),
            'price_type' => $this->whenLoaded('priceType', fn () => $this->priceType ? [
                'id' => $this->priceType->id,
                'name' => $this->priceType->name,
            ] : null),
            'pickup_location' => $this->whenLoaded('pickupLocation', fn () => $this->pickupLocation ? [
                'id' => $this->pickupLocation->id,
                'name' => $this->pickupLocation->name,
            ] : null),
            'dropoff_location' => $this->whenLoaded('dropoffLocation', fn () => $this->dropoffLocation ? [
                'id' => $this->dropoffLocation->id,
                'name' => $this->dropoffLocation->name,
            ] : null),
            'line_items' => $this->whenLoaded('lineItems', fn () => $this->lineItems->map(fn ($item) => [
                'kind' => $item->kind,
                'label' => $item->label,
                'amount_cents' => (int) $item->amount_cents,
                'amount_formatted' => Money::formatDecimalFromCents((int) $item->amount_cents),
                'quantity' => $item->quantity,
            ])),
            'rental_options' => $this->whenLoaded('rentalOptions', fn () => $this->rentalOptions->map(fn ($row) => [
                'name' => $row->relationLoaded('rentalOption') ? $row->rentalOption?->name : null,
                'quantity' => $row->quantity,
                'total_cents' => (int) $row->total_cents,
                'total_formatted' => Money::formatDecimalFromCents((int) $row->total_cents),
            ])),
            'change_requests' => BookingChangeRequestResource::collection(
                $this->whenLoaded('changeRequests')
            ),
        ];
    }
}
