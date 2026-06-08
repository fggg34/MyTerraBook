<?php

namespace App\Services\Email;

use App\Models\GuestHouseBooking;
use App\Models\Setting;
use App\Support\Money;

class GuestHouseBookingEmailPayload
{
    /**
     * @return array<string, string>
     */
    public static function for(GuestHouseBooking $booking): array
    {
        $booking->loadMissing('guestHouse');

        $symbol = (string) data_get(Setting::getValue('shop.currency', ['symbol' => '€']), 'symbol', '€');

        return [
            'booking_reference' => (string) $booking->booking_reference,
            'listing_name' => (string) ($booking->guestHouse?->name ?? 'Guest house'),
            'guest_name' => (string) $booking->guest_name,
            'guest_email' => (string) $booking->guest_email,
            'check_in' => $booking->check_in?->format('d M Y') ?? '',
            'check_out' => $booking->check_out?->format('d M Y') ?? '',
            'guests_count' => (string) $booking->guests_count,
            'total' => $symbol.Money::formatDecimalFromCents((int) $booking->total_amount),
            'reason' => (string) ($booking->cancellation_reason ?? ''),
        ];
    }
}
