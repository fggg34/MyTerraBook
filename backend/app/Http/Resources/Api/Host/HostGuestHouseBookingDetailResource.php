<?php

namespace App\Http\Resources\Api\Host;

use App\Http\Resources\Api\BookingChangeRequestResource;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\GuestHouseBooking */
class HostGuestHouseBookingDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kind' => 'guesthouse',
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,
            'confirmation_url' => $this->confirmation_url,
            'status' => $this->status->value,
            'guest_name' => $this->guest_name,
            'guest_email' => $this->guest_email,
            'guest_phone' => $this->guest_phone,
            'check_in' => $this->check_in->toDateString(),
            'check_out' => $this->check_out->toDateString(),
            'nights' => $this->nights,
            'guests_count' => $this->guests_count,
            'special_requests' => $this->special_requests,
            'coupon_code' => $this->coupon_code,
            'total_amount' => (int) $this->total_amount,
            'total_formatted' => '€ '.Money::formatDecimalFromCents((int) $this->total_amount),
            'base_total' => (int) $this->base_total,
            'cleaning_fee' => (int) $this->cleaning_fee,
            'security_deposit' => (int) $this->security_deposit,
            'discount_amount' => (int) $this->discount_amount,
            'tax_amount' => (int) $this->tax_amount,
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'guest_house' => $this->whenLoaded('guestHouse', fn () => $this->guestHouse ? [
                'id' => $this->guestHouse->id,
                'name' => $this->guestHouse->name,
                'slug' => $this->guestHouse->slug,
                'city' => $this->guestHouse->city,
                'thumbnail' => $this->guestHouse->thumbnail,
            ] : null),
            'change_requests' => BookingChangeRequestResource::collection(
                $this->whenLoaded('changeRequests')
            ),
        ];
    }
}
