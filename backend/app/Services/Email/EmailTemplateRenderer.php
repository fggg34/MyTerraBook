<?php

namespace App\Services\Email;

use App\Models\EmailTemplate;

class EmailTemplateRenderer
{
    public function __construct(
        private readonly EmailSettingsService $settings,
    ) {}

    /**
     * Render a template into the data the brand layout expects.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function render(EmailTemplate $template, array $data = []): array
    {
        $brand = $this->settings->load();
        $context = array_merge($this->globals($brand), $this->stringify($data));

        return [
            'subject' => $this->substitute($template->subject, $context),
            'preheader' => $this->substitute((string) $template->preheader, $context),
            'heading' => $this->substitute((string) $template->heading, $context),
            'greeting' => $this->substitute((string) $template->greeting, $context),
            'bodyHtml' => $this->substitute((string) $template->body_html, $context, true),
            'ctaLabel' => $this->substitute((string) $template->cta_label, $context),
            'ctaUrl' => $this->substitute((string) $template->cta_url_template, $context),
            'footerNote' => $this->substitute((string) $template->footer_note, $context),
            // Brand / layout settings
            'brandName' => $brand['brand_name'],
            'accentColor' => $brand['accent_color'],
            'headingColor' => $brand['heading_color'],
            'logoMode' => $brand['logo_mode'],
            'logoUrl' => $this->settings->resolveLogoUrl($brand['logo_url']),
            'footerText' => $this->substitute((string) $brand['footer_text'], $context),
            'supportEmail' => $brand['support_email'] !== '' ? $brand['support_email'] : $brand['reply_to'],
            'companyAddress' => $brand['company_address'],
            'year' => date('Y'),
        ];
    }

    /**
     * @param  array<string, string>  $brand
     * @return array<string, string>
     */
    private function globals(array $brand): array
    {
        return [
            'brand_name' => $brand['brand_name'],
            'frontend_url' => rtrim((string) config('app.frontend_url', config('app.url')), '/'),
            'support_email' => $brand['support_email'] !== '' ? $brand['support_email'] : $brand['sender_email'],
            'admin_url' => rtrim((string) config('app.url'), '/').'/admin',
            'current_year' => date('Y'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function stringify(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                $result[$key] = '';

                continue;
            }

            if (is_bool($value)) {
                $result[$key] = $value ? 'yes' : 'no';

                continue;
            }

            $result[$key] = (string) $value;
        }

        return $result;
    }

    /**
     * @param  array<string, string>  $context
     */
    private function substitute(string $text, array $context, bool $html = false): string
    {
        if ($text === '') {
            return '';
        }

        return (string) preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',
            function (array $matches) use ($context, $html): string {
                $key = $matches[1];

                if (! array_key_exists($key, $context)) {
                    return $matches[0];
                }

                $value = $context[$key];

                return $html ? e($value) : $value;
            },
            $text,
        );
    }
}
