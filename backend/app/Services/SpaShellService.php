<?php

namespace App\Services;

use App\Support\SiteColors;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SpaShellService
{
    /**
     * How long a rendered storefront shell is reused. Editing CMS content or
     * flipping Coming Soon clears it, so this is only a floor for traffic
     * spikes, not a delay on publishing.
     */
    private const CACHE_TTL = 600;

    public function __construct(
        private readonly SiteContentService $siteContent,
    ) {}

    /**
     * Both shell variants. The gate state is part of the key so toggling
     * Coming Soon can never serve the wrong page from cache.
     *
     * @return list<string>
     */
    public static function cacheKeys(): array
    {
        return ['spa.shell.open', 'spa.shell.locked'];
    }

    /**
     * @return array{siteContent: array<string, array<string, mixed>>, homepage: array<string, mixed>}
     */
    public function bootstrapPayload(): array
    {
        return $this->siteContent->bootstrapPayload();
    }

    public function renderShell(): string
    {
        $indexPath = config('spa.index_path');

        if (! is_string($indexPath) || $indexPath === '' || ! File::isFile($indexPath)) {
            Log::warning('SPA index.html not found for bootstrap shell.', ['path' => $indexPath]);

            // Left uncached so the storefront recovers as soon as the build lands.
            return $this->fallbackHtml();
        }

        $unlocked = app(SitePreviewService::class)->publicState()['guestUnlocked'];
        $key = $unlocked ? 'spa.shell.open' : 'spa.shell.locked';

        $cached = Cache::get($key);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $html = $this->buildShell($indexPath);

        if ($html === null) {
            // Never 500 the storefront: serve the static shell so the SPA boots
            // and fetches content from the API itself. Not cached, because this
            // copy has no injected content or gate state.
            return str_replace((string) config('spa.bootstrap_marker'), '', File::get($indexPath));
        }

        Cache::put($key, $html, self::CACHE_TTL);

        return $html;
    }

    private function buildShell(string $indexPath): ?string
    {
        $marker = (string) config('spa.bootstrap_marker');
        $html = File::get($indexPath);

        try {
            $payload = $this->bootstrapPayload();
        } catch (\Throwable $e) {
            Log::warning('SPA bootstrap payload failed; serving shell without injected content.', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }

        if (! str_contains($html, $marker)) {
            Log::warning('SPA bootstrap marker missing from index.html.', [
                'path' => $indexPath,
                'marker' => $marker,
            ]);

            return $this->injectBootstrap($html, $payload);
        }

        $script = $this->buildBootstrapScript($payload);
        $html = str_replace($marker, $script, $html);

        return $this->applyHeadMetadata($html, $payload);
    }

    /**
     * @param  array{siteContent: array<string, array<string, mixed>>, homepage: array<string, mixed>}  $payload
     */
    private function buildBootstrapScript(array $payload): string
    {
        $json = json_encode(
            $payload,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        return '<script id="myterrabook-site-bootstrap">window.__MYTERRABOOK_BOOTSTRAP__='.$json.';</script>';
    }

    /**
     * @param  array{siteContent: array<string, array<string, mixed>>, homepage: array<string, mixed>}  $payload
     */
    private function injectBootstrap(string $html, array $payload): string
    {
        $script = $this->buildBootstrapScript($payload);

        if (str_contains($html, '</head>')) {
            return str_replace('</head>', $script."\n  </head>", $html);
        }

        if (preg_match('/<body[^>]*>/i', $html, $matches, PREG_OFFSET_CAPTURE)) {
            $pos = $matches[0][1];

            return substr_replace($html, $matches[0][0]."\n".$script, $pos, strlen($matches[0][0]));
        }

        return $script.$html;
    }

    /**
     * @param  array{siteContent: array<string, array<string, mixed>>, homepage: array<string, mixed>}  $payload
     */
    private function applyHeadMetadata(string $html, array $payload): string
    {
        $branding = $payload['siteContent']['global']['branding'] ?? [];
        $favicon = is_array($branding) ? ($branding['favicon'] ?? '') : '';

        if (is_string($favicon) && $favicon !== '') {
            $html = preg_replace(
                '/<link[^>]+rel=["\']?(?:shortcut )?icon["\']?[^>]*>/i',
                '<link rel="icon" href="'.e($favicon, false).'" />',
                $html,
                1,
            ) ?? $html;
        }

        $prefix = is_array($branding) ? ($branding['prefix'] ?? 'My') : 'My';
        $accent = is_array($branding) ? ($branding['accent'] ?? 'Terra') : 'Terra';
        $suffix = is_array($branding) ? ($branding['suffix'] ?? 'Book') : 'Book';
        $title = $prefix.$accent.$suffix;

        $html = preg_replace('/<title>.*?<\/title>/is', '<title>'.e($title, false).'</title>', $html, 1) ?? $html;

        return $this->applySiteColorStyles($html, $payload);
    }

    /**
     * @param  array{siteContent: array<string, array<string, mixed>>, homepage: array<string, mixed>}  $payload
     */
    private function applySiteColorStyles(string $html, array $payload): string
    {
        $colors = $payload['siteContent']['global']['colors'] ?? [];
        if (! is_array($colors)) {
            return $html;
        }

        $css = SiteColors::toRootCss($colors);
        if ($css === '') {
            return $html;
        }

        $style = '<style id="myterrabook-site-colors">'.$css.'</style>';

        if (str_contains($html, '</head>')) {
            return str_replace('</head>', $style."\n  </head>", $html);
        }

        return $html;
    }

    private function fallbackHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MyTerraBook</title>
  </head>
  <body>
    <p>Storefront is temporarily unavailable. Please try again shortly.</p>
  </body>
</html>
HTML;
    }
}
