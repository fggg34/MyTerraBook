<?php

namespace App\Services\Email;

use App\Models\GuestHouseBooking;
use App\Support\PaymentEmailSummary;

class GuestHouseBookingEmailPayload
{
    /**
     * @return array<string, string>
     */
    public static function for(GuestHouseBooking $booking): array
    {
        $booking->loadMissing('guestHouse');

        $payment = PaymentEmailSummary::forGuestHouseBooking($booking);

        return [
            'booking_reference' => (string) $booking->booking_reference,
            'listing_name' => (string) ($booking->guestHouse?->name ?? 'Guest house'),
            'guest_name' => (string) $booking->guest_name,
            'guest_email' => (string) $booking->guest_email,
            'check_in' => $booking->check_in?->format('d M Y') ?? '',
            'check_out' => $booking->check_out?->format('d M Y') ?? '',
            'guests_count' => (string) $booking->guests_count,
            'total' => $payment['total'],
            'total_isk' => $payment['total_isk'],
            'paid_online' => $payment['paid_online'],
            'cash_due_on_arrival' => $payment['cash_due_on_arrival'],
            'reason' => (string) ($booking->cancellation_reason ?? ''),
        ];
    }
}
