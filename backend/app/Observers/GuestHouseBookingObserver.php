<?php

namespace App\Observers;

use App\Enums\GuestHouseBookingStatus;
use App\Models\GuestHouseBooking;
use App\Services\Email\GuestHouseBookingEmailNotifier;

class GuestHouseBookingObserver
{
    public function __construct(
        private readonly GuestHouseBookingEmailNotifier $bookingEmails,
    ) {}

    public function updated(GuestHouseBooking $booking): void
    {
        if (! $booking->wasChanged('status')) {
            return;
        }

        $original = $booking->getOriginal('status');
        $from = $original instanceof GuestHouseBookingStatus ? $original : GuestHouseBookingStatus::tryFrom((string) $original);
        $to = $booking->status;

        if ($from === null || $to === null) {
            return;
        }

        $this->bookingEmails->notifyStatusChanged($booking, $from, $to);
    }
}
