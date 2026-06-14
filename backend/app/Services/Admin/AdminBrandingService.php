<?php

namespace App\Services\Admin;

use App\Models\Setting;
use App\Services\SiteContentService;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class AdminBrandingService
{
    public function __construct(
        private readonly SiteContentService $siteContent,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function branding(): array
    {
        $global = $this->siteContent->pageContent('global');

        return is_array($global['branding'] ?? null) ? $global['branding'] : [];
    }

    public function logoHtml(): Htmlable
    {
        $branding = $this->branding();
        $mode = (string) ($branding['logoMode'] ?? 'text');
        $logoImage = $branding['logoImage'] ?? null;

        if ($mode === 'image' && is_string($logoImage) && $logoImage !== '') {
            $alt = $this->logoAltText($branding);

            return new HtmlString(
                '<img src="'.e($logoImage).'" alt="'.e($alt).'" class="tb-admin-brand-logo__image">'
            );
        }

        $legacyLogo = $this->legacyBackendLogoUrl();

        if ($legacyLogo !== null) {
            return new HtmlString(
                '<img src="'.e($legacyLogo).'" alt="'.e(config('app.name', 'MyTerraBook')).'" class="tb-admin-brand-logo__image">'
            );
        }

        return new HtmlString(view('filament.panel.brand-logo', [
            'prefix' => (string) ($branding['prefix'] ?? 'My'),
            'accent' => (string) ($branding['accent'] ?? 'Terra'),
            'suffix' => (string) ($branding['suffix'] ?? 'Book'),
        ])->render());
    }

    public function faviconUrl(): ?string
    {
        $branding = $this->branding();
        $favicon = $branding['favicon'] ?? null;

        if (is_string($favicon) && $favicon !== '') {
            return $favicon;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $branding
     */
    private function logoAltText(array $branding): string
    {
        return (string) ($branding['prefix'] ?? 'My')
            .(string) ($branding['accent'] ?? 'Terra')
            .(string) ($branding['suffix'] ?? 'Book');
    }

    private function legacyBackendLogoUrl(): ?string
    {
        $path = (string) data_get(Setting::getValue('orders.backend_logo_180', ['path' => '']), 'path', '');

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '/'])) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }
}
