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
        // Local Laravel does not serve frontend/dist. The Vite app at FRONTEND_URL
        // does, so the calendar page uses that iframe instead of a missing script.
        if (app()->environment('local')) {
            return [null, null];
        }

        $found = self::readManifestRecord();
        if ($found === null) {
            return [null, null];
        }

        $manifest = $found['manifest'];
        $entry = $manifest['calendar-embed.html'] ?? null;
        if (! is_array($entry) || ! isset($entry['file']) || ! is_string($entry['file']) || $entry['file'] === '') {
            return [null, null];
        }

        $root = self::manifestRoot($found['path']);
        if (! File::isFile($root.'/'.$entry['file'])) {
            return [null, null];
        }

        $base = rtrim(AdminCalendarEmbed::resolvePublicFrontendUrl(), '/');
        $js = $base.'/'.ltrim($entry['file'], '/');
        $css = null;
        foreach (self::collectCssFiles($manifest, $entry) as $file) {
            if (File::isFile($root.'/'.$file)) {
                $css = $base.'/'.ltrim($file, '/');
                break;
            }
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
     * @return array{path: string, manifest: array<string, mixed>}|null
     */
    private static function readManifestRecord(): ?array
    {
        foreach (self::manifestPaths() as $path) {
            if (! File::isFile($path)) {
                continue;
            }

            $decoded = json_decode(File::get($path), true);
            if (! is_array($decoded)) {
                continue;
            }

            return [
                'path' => $path,
                'manifest' => $decoded,
            ];
        }

        return null;
    }

    private static function manifestRoot(string $manifestPath): string
    {
        $dir = dirname($manifestPath);

        return basename($dir) === '.vite' ? dirname($dir) : $dir;
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

    public static function resolveEmbedHtmlPath(): ?string
    {
        foreach (self::embedHtmlPaths() as $path) {
            if (File::isFile($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private static function embedHtmlPaths(): array
    {
        $paths = [];
        $indexPath = config('spa.index_path');

        if (is_string($indexPath) && $indexPath !== '') {
            $paths[] = dirname($indexPath).'/calendar-embed.html';
        }

        $paths[] = dirname(base_path()).'/frontend/dist/calendar-embed.html';

        return $paths;
    }
}
