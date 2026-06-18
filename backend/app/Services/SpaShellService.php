<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SpaShellService
{
    public function __construct(
        private readonly SiteContentService $siteContent,
    ) {}

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
        $marker = (string) config('spa.bootstrap_marker');

        if (! is_string($indexPath) || $indexPath === '' || ! File::isFile($indexPath)) {
            Log::warning('SPA index.html not found for bootstrap shell.', ['path' => $indexPath]);

            return $this->fallbackHtml();
        }

        $html = File::get($indexPath);

        if (! str_contains($html, $marker)) {
            Log::warning('SPA bootstrap marker missing from index.html.', [
                'path' => $indexPath,
                'marker' => $marker,
            ]);

            return $this->injectBootstrap($html, $this->bootstrapPayload());
        }

        $payload = $this->bootstrapPayload();
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
