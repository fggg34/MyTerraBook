<?php

namespace App\Http\Resources\Api;

use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GuestHouseBooking */
class GuestHouseBookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,
            'status' => $this->status->value,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'guest_phone' => $this->guest_phone,
            'check_in' => $this->check_in->toDateString(),
            'check_out' => $this->check_out->toDateString(),
            'nights' => $this->nights,
            'guests_count' => $this->guests_count,
            'base_total_cents' => $this->base_total,
            'cleaning_fee_cents' => $this->cleaning_fee,
            'security_deposit_cents' => $this->security_deposit,
            'discount_amount_cents' => $this->discount_amount,
            'tax_amount_cents' => $this->tax_amount,
            'total_amount_cents' => $this->total_amount,
            'total_formatted' => '€ '.Money::formatDecimalFromCents($this->total_amount),
            'special_requests' => $this->special_requests,
            'coupon_code' => $this->coupon_code,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'guest_house' => $this->whenLoaded('guestHouse', fn () => [
                'id' => $this->guestHouse->id,
                'name' => $this->guestHouse->name,
                'slug' => $this->guestHouse->slug,
                'city' => $this->guestHouse->city,
                'thumbnail' => $this->guestHouse->thumbnail,
            ]),
        ];
    }
}
