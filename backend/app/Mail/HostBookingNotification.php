<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HostBookingNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(public array $data) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Booking — Guest Arriving with Cash Balance');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.host_booking_notification',
            with: ['d' => $this->data],
        );
    }
}
