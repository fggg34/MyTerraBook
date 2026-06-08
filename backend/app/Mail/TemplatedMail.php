<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Services\Email\EmailSettingsService;
use App\Services\Email\EmailTemplateRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TemplatedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /** @var array<string, mixed>|null */
    private ?array $renderedCache = null;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $templateKey,
        public array $data = [],
    ) {}

    public function envelope(): Envelope
    {
        $rendered = $this->rendered();
        $settings = app(EmailSettingsService::class)->load();

        $envelope = new Envelope(
            from: new Address($settings['sender_email'], $settings['sender_name']),
            subject: $rendered['subject'] !== '' ? $rendered['subject'] : (string) config('app.name', 'MyTerraBook'),
        );

        if (($settings['reply_to'] ?? '') !== '') {
            $envelope->replyTo = [new Address($settings['reply_to'])];
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.layouts.brand',
            with: $this->rendered(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function rendered(): array
    {
        if ($this->renderedCache !== null) {
            return $this->renderedCache;
        }

        $template = EmailTemplate::findByKey($this->templateKey);

        if ($template === null) {
            return $this->renderedCache = [
                'subject' => (string) config('app.name', 'MyTerraBook'),
                'bodyHtml' => '',
            ];
        }

        return $this->renderedCache = app(EmailTemplateRenderer::class)->render($template, $this->data);
    }
}
