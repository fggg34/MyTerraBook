<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class AdminCalendarEmbedAssets
{
    /**
     * @return array{0: string|null, 1: string|null} JS and CSS URLs on the public storefront
     */
    public static function resolve(): array
    {
        $manifest = self::readManifest();
        if ($manifest === null) {
            return [null, null];
        }

        $entry = $manifest['calendar-embed.html'] ?? null;
        if (! is_array($entry) || ! isset($entry['file'])) {
            return [null, null];
        }

        $base = rtrim(AdminCalendarEmbed::resolvePublicFrontendUrl(), '/');
        $js = $base.'/'.$entry['file'];
        $cssFiles = self::collectCssFiles($manifest, $entry);
        $css = $cssFiles[0] ?? null;
        if ($css !== null) {
            $css = $base.'/'.$css;
        }

        return [$js, $css];
    }

    /**
     * @param  array<string, mixed>  $manifest
     * @param  array<string, mixed>  $entry
     * @return list<string>
     */
    private static function collectCssFiles(array $manifest, array $entry): array
    {
        $files = [];

        if (isset($entry['css']) && is_array($entry['css'])) {
            foreach ($entry['css'] as $file) {
                if (is_string($file) && $file !== '') {
                    $files[] = $file;
                }
            }
        }

        if (isset($entry['imports']) && is_array($entry['imports'])) {
            foreach ($entry['imports'] as $importKey) {
                if (! is_string($importKey)) {
                    continue;
                }

                $import = $manifest[$importKey] ?? null;
                if (! is_array($import)) {
                    continue;
                }

                foreach (self::collectCssFiles($manifest, $import) as $file) {
                    $files[] = $file;
                }
            }
        }

        return array_values(array_unique($files));
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function readManifest(): ?array
    {
        foreach (self::manifestPaths() as $path) {
            if (! File::isFile($path)) {
                continue;
            }

            $decoded = json_decode(File::get($path), true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function manifestPaths(): array
    {
        $paths = [];
        $indexPath = config('spa.index_path');

        if (is_string($indexPath) && $indexPath !== '') {
            $dir = dirname($indexPath);
            $paths[] = $dir.'/.vite/manifest.json';
            $paths[] = $dir.'/manifest.json';
        }

        $paths[] = dirname(base_path()).'/frontend/dist/.vite/manifest.json';
        $paths[] = dirname(base_path()).'/frontend/dist/manifest.json';

        return $paths;
    }
}
