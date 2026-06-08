<?php

namespace App\Observers;

use App\Enums\GuestHouseBookingStatus;
use App\Models\GuestHouseBooking;
use App\Services\Email\EmailService;
use App\Services\Email\GuestHouseBookingEmailPayload;

class GuestHouseBookingObserver
{
    public function __construct(
        private readonly EmailService $email,
    ) {}

    public function updated(GuestHouseBooking $booking): void
    {
        if (! $booking->wasChanged('status')) {
            return;
        }

        if (! $booking->guest_email) {
            return;
        }

        $payload = GuestHouseBookingEmailPayload::for($booking);

        match ($booking->status) {
            GuestHouseBookingStatus::Confirmed => $this->email->send('gh_booking_confirmed', $booking->guest_email, $payload),
            GuestHouseBookingStatus::Cancelled => $this->email->send('gh_booking_declined', $booking->guest_email, $payload),
            default => null,
        };
    }
}
