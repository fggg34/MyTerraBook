<?php

namespace App\Services\Email;

use App\Enums\GuestHouseBookingStatus;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Services\Documents\RentalTermsPdf;
use Illuminate\Mail\Mailables\Attachment;

class GuestHouseBookingEmailNotifier
{
    public function __construct(
        private readonly EmailService $email,
        private readonly EmailSettingsService $emailSettings,
        private readonly RentalTermsPdf $rentalTermsPdf,
    ) {}

    public function notifyCreated(GuestHouseBooking $booking, GuestHouse $house): void
    {
        $house->loadMissing('host');
        $payload = GuestHouseBookingEmailPayload::for($booking);

        if ($booking->guest_email) {
            $guestTemplate = $booking->status === GuestHouseBookingStatus::Confirmed
                ? 'gh_booking_confirmed'
                : 'gh_booking_received';
            $this->email->send($guestTemplate, $booking->guest_email, $payload, $this->termsAttachments($booking));
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
                GuestHouseBookingStatus::Confirmed => $this->email->send('gh_booking_confirmed', $booking->guest_email, $payload, $this->termsAttachments($booking)),
                GuestHouseBookingStatus::Cancelled => $this->email->send('gh_booking_declined', $booking->guest_email, $payload, $this->termsAttachments($booking)),
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

    /**
     * @return array<int, Attachment>
     */
    private function termsAttachments(GuestHouseBooking $booking): array
    {
        $attachment = $this->rentalTermsPdf->attachment(
            (string) $booking->booking_reference,
            $booking->guest_name,
            $booking->resolvedRentalTermsHtml(),
        );

        return $attachment === null ? [] : [$attachment];
    }
}
