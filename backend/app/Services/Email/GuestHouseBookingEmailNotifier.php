<?php

namespace App\Services\Email;

use App\Enums\GuestHouseBookingStatus;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;

class GuestHouseBookingEmailNotifier
{
    public function __construct(
        private readonly EmailService $email,
        private readonly EmailSettingsService $emailSettings,
    ) {}

    public function notifyCreated(GuestHouseBooking $booking, GuestHouse $house): void
    {
        $house->loadMissing('host');
        $payload = GuestHouseBookingEmailPayload::for($booking);

        if ($booking->guest_email) {
            $guestTemplate = $booking->status === GuestHouseBookingStatus::Confirmed
                ? 'gh_booking_confirmed'
                : 'gh_booking_received';
            $this->email->send($guestTemplate, $booking->guest_email, $payload);
        }

        if ($hostEmail = $house->host?->email) {
            $this->email->send('gh_booking_new_host', $hostEmail, $payload);
        }

        $adminEmail = $this->emailSettings->getAdminEmail();
        if ($adminEmail !== '') {
            $this->email->send('gh_booking_new_admin', $adminEmail, $payload + [
                'admin_url' => rtrim((string) config('app.url'), '/').'/admin',
            ]);
        }
    }

    public function notifyStatusChanged(GuestHouseBooking $booking, GuestHouseBookingStatus $from, GuestHouseBookingStatus $to): void
    {
        if ($from === $to) {
            return;
        }

        $booking->loadMissing('guestHouse.host');
        $payload = GuestHouseBookingEmailPayload::for($booking);

        if ($booking->guest_email) {
            match ($to) {
                GuestHouseBookingStatus::Confirmed => $this->email->send('gh_booking_confirmed', $booking->guest_email, $payload),
                GuestHouseBookingStatus::Cancelled => $this->email->send('gh_booking_declined', $booking->guest_email, $payload),
                default => null,
            };
        }

        if ($hostEmail = $booking->guestHouse?->host?->email) {
            match ($to) {
                GuestHouseBookingStatus::Confirmed => $this->email->send('gh_booking_confirmed_host', $hostEmail, $payload),
                GuestHouseBookingStatus::Cancelled => $this->email->send('gh_booking_declined_host', $hostEmail, $payload),
                default => null,
            };
        }
    }
}
