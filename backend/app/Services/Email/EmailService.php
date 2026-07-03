<?php

namespace App\Services\Email;

use App\Mail\TemplatedMail;
use App\Models\EmailLog;
use App\Models\EmailTemplate;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function __construct(
        private readonly EmailTemplateRenderer $renderer,
    ) {}

    /**
     * Send a templated email. Fails safe: never throws into the calling flow.
     *
     * @param  string|array<int, string>  $recipients
     * @param  array<string, mixed>  $data
     * @param  array<int, Attachment>  $attachments
     */
    public function send(string $templateKey, string|array $recipients, array $data = [], array $attachments = []): bool
    {
        $template = EmailTemplate::findByKey($templateKey);

        if ($template === null) {
            Log::warning('Email template not found', ['key' => $templateKey]);

            return false;
        }

        if (! $template->is_enabled) {
            return false;
        }

        $rendered = $this->renderer->render($template, $data);
        $subject = (string) ($rendered['subject'] ?? '');
        $sentAny = false;

        foreach ($this->normalizeRecipients($recipients) as $recipient) {
            try {
                Mail::to($recipient)->sendNow(new TemplatedMail($templateKey, $data, $attachments));

                EmailLog::query()->create([
                    'template_key' => $templateKey,
                    'recipient' => $recipient,
                    'subject' => $subject,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                $sentAny = true;
            } catch (\Throwable $e) {
                Log::error('Templated email failed', [
                    'key' => $templateKey,
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                ]);

                EmailLog::query()->create([
                    'template_key' => $templateKey,
                    'recipient' => $recipient,
                    'subject' => $subject,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sentAny;
    }

    public function isEnabled(string $templateKey): bool
    {
        return (bool) EmailTemplate::query()
            ->where('key', $templateKey)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * @param  string|array<int, string>  $recipients
     * @return array<int, string>
     */
    private function normalizeRecipients(string|array $recipients): array
    {
        $list = is_array($recipients) ? $recipients : [$recipients];

        return collect($list)
            ->map(fn ($email): string => trim((string) $email))
            ->filter(fn (string $email): bool => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }
}
