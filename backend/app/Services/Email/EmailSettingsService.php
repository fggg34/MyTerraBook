<?php

namespace App\Services\Email;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmailSettingsService
{
    /**
     * Default brand values used when no setting row exists yet.
     *
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return [
            'brand_name' => (string) config('app.name', 'MyTerraBook'),
            'sender_name' => (string) config('mail.from.name', config('app.name', 'MyTerraBook')),
            'sender_email' => (string) config('mail.from.address', 'hello@myterrabook.com'),
            'reply_to' => '',
            'support_email' => '',
            'logo_mode' => 'text',
            'logo_url' => '',
            'accent_color' => '#45a06a',
            'heading_color' => '#0f2036',
            'footer_text' => 'You are receiving this email because you have an account or a booking with '.config('app.name', 'MyTerraBook').'.',
            'company_address' => '',
            'test_recipient_email' => '',
        ];
    }

    public function getTestRecipient(): string
    {
        return $this->get('test_recipient_email');
    }

    public function saveTestRecipient(string $email): void
    {
        Setting::putValue('email.test_recipient_email', ['value' => trim($email)]);
    }

    /**
     * @return array<string, string>
     */
    public function load(): array
    {
        $defaults = $this->defaults();
        $state = [];

        foreach ($defaults as $key => $default) {
            $stored = Setting::getValue('email.'.$key, null);
            $state[$key] = is_array($stored)
                ? (string) ($stored['value'] ?? $default)
                : ($stored === null ? $default : (string) $stored);
        }

        return $state;
    }

    /**
     * @return array<string, string>
     */
    public function loadForForm(): array
    {
        $state = $this->load();
        $state['logo_url'] = $this->normalizeLogoPathForUpload($state['logo_url']);

        return $state;
    }

    public function resolveLogoUrl(string $logoUrl): string
    {
        $logoUrl = trim($logoUrl);

        if ($logoUrl === '') {
            return '';
        }

        if (Str::startsWith($logoUrl, ['http://', 'https://'])) {
            return $logoUrl;
        }

        return Storage::disk('public')->url($logoUrl);
    }

    public function normalizeLogoPathForUpload(string $logoUrl): string
    {
        $logoUrl = trim($logoUrl);

        if ($logoUrl === '') {
            return '';
        }

        if (! Str::startsWith($logoUrl, ['http://', 'https://'])) {
            return $logoUrl;
        }

        $path = parse_url($logoUrl, PHP_URL_PATH);

        if (! is_string($path) || ! str_contains($path, '/storage/')) {
            return '';
        }

        return ltrim(Str::after($path, '/storage/'), '/');
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function save(array $state): void
    {
        if (array_key_exists('logo_url', $state)) {
            $state['logo_url'] = $this->normalizeLogoPathForStorage($state['logo_url']);
        }

        foreach (array_keys($this->defaults()) as $key) {
            if (! array_key_exists($key, $state)) {
                continue;
            }

            Setting::putValue('email.'.$key, ['value' => (string) $state[$key]]);
        }
    }

    private function normalizeLogoPathForStorage(mixed $logoUrl): string
    {
        if (is_array($logoUrl)) {
            $logoUrl = $logoUrl[0] ?? '';
        }

        $logoUrl = trim((string) $logoUrl);

        if ($logoUrl === '') {
            return '';
        }

        if (Str::startsWith($logoUrl, ['http://', 'https://'])) {
            return $this->normalizeLogoPathForUpload($logoUrl);
        }

        return $logoUrl;
    }

    public function get(string $key): string
    {
        return $this->load()[$key] ?? '';
    }
}
