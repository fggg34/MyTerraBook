<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{name: string, email: string, message: string}  $payload
     */
    public function __construct(
        public array $payload,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'MyTerraBook contact: '.$this->payload['name'],
            replyTo: [$this->payload['email']],
        );
    }

    public function content(): Content
    {
        $name = (string) ($this->payload['name'] ?? '');
        $email = (string) ($this->payload['email'] ?? '');
        $message = (string) ($this->payload['message'] ?? '');

        return new Content(
            view: 'mail.layouts.brand',
            with: [
                'heading' => 'New contact message',
                'greeting' => 'Hello,',
                'preheader' => 'A visitor sent a message through the contact form.',
                'bodyHtml' => '<p><strong>From:</strong> '.e($name).' ('.e($email).')</p>'
                    .'<p>'.nl2br(e($message)).'</p>',
            ],
        );
    }
}
