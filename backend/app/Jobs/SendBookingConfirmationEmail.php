<?php

namespace App\Jobs;

use App\Mail\GuestBookingConfirmation;
use App\Mail\HostBookingNotification;
use App\Models\GuestHouseBooking;
use App\Models\RapydPayment;
use App\Support\PaymentEmailSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Dispatched after Rapyd confirms the 20% platform fee payment. Sends two
 * separate emails describing the split: 20% paid online, 80% cash on arrival.
 */
class SendBookingConfirmationEmail
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public int $rapydPaymentId,
        public bool $notifyCashReceived = false,
    ) {}

    public function handle(): void
    {
        $payment = RapydPayment::query()->find($this->rapydPaymentId);
        if (! $payment) {
            return;
        }

        $booking = GuestHouseBooking::query()->with(['guestHouse.host', 'user'])->find($payment->order_id);

        $guestHouse = $booking?->guestHouse;
        $host = $guestHouse?->host;

        $summary = PaymentEmailSummary::fromAmounts(
            (float) $payment->total_price,
            (float) $payment->platform_fee,
            (float) $payment->cash_due_on_arrival,
        );

        $data = [
            'booking_reference' => $booking?->booking_reference ?? ('ORDER-'.$payment->order_id),
            'listing_name' => $guestHouse?->name ?? 'Your booking',
            'check_in' => optional($booking?->check_in)->toDateString(),
            'check_out' => optional($booking?->check_out)->toDateString(),
            'currency' => 'ISK',
            'total_price' => (float) $payment->total_price,
            'platform_fee' => (float) $payment->platform_fee,
            'cash_due_on_arrival' => (float) $payment->cash_due_on_arrival,
            'total_isk' => $summary['total_isk'],
            'paid_online' => $summary['paid_online'],
            'cash_due_on_arrival_formatted' => $summary['cash_due_on_arrival'],
            'guest_name' => $booking?->guest_name ?? $payment->user?->name ?? 'Guest',
            'guest_email' => $booking?->guest_email ?? $payment->user?->email,
            'guest_phone' => $booking?->guest_phone,
            'host_name' => $host?->name ?? 'your host',
            'host_email' => $host?->email,
            'host_phone' => $host?->phone ?? null,
            'cash_received' => $this->notifyCashReceived,
        ];

        if ($data['guest_email']) {
            Mail::to($data['guest_email'])->sendNow(new GuestBookingConfirmation($data));
        }

        if (! $this->notifyCashReceived && $data['host_email']) {
            Mail::to($data['host_email'])->sendNow(new HostBookingNotification($data));
        }
    }
}
