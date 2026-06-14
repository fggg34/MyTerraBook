<?php

use Dotenv\Dotenv;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

/**
 * The app is served under a URL prefix (e.g. /backend). Expose that prefix to Symfony as
 * the base URL so routes still match (/admin, /api/…) while request()->url() keeps the
 * prefix , required for signed-URL validation (Livewire file uploads/previews) to match the
 * signatures generated with URL::forceRootUrl(.../backend).
 * Live servers often omit the path in APP_URL or cache a wrong .env , prefer SCRIPT_NAME.
 */
if (PHP_SAPI !== 'cli' && is_file(__DIR__.'/../.env')) {
    Dotenv::createImmutable(__DIR__.'/..')->safeLoad();
}

if (PHP_SAPI !== 'cli') {
    $prefix = null;

    $explicit = $_ENV['LARAVEL_URL_PREFIX'] ?? getenv('LARAVEL_URL_PREFIX');
    if (is_string($explicit) && trim($explicit) !== '') {
        $prefix = trim($explicit, '/');
    }

    // Filesystem path works when SCRIPT_NAME is wrong (some proxies / PHP handlers).
    if ($prefix === null || $prefix === '') {
        $sf = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');
        if (preg_match('#/([^/]+)/public/index\.php$#', $sf, $m)) {
            $prefix = $m[1];
        }
    }

    if ($prefix === null || $prefix === '') {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        foreach (['#^/(.+?)/public/index\.php$#', '#^(.+?)/public/index\.php$#'] as $pattern) {
            if (preg_match($pattern, $script, $m)) {
                $prefix = $m[1];
                break;
            }
        }
    }

    if ($prefix === null || $prefix === '') {
        $self = $_SERVER['PHP_SELF'] ?? '';
        foreach (['#^/(.+?)/public/index\.php$#', '#^(.+?)/public/index\.php$#'] as $pattern) {
            if (preg_match($pattern, $self, $m)) {
                $prefix = $m[1];
                break;
            }
        }
    }

    if ($prefix === null || $prefix === '') {
        $appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL');
        if (is_string($appUrl) && $appUrl !== '') {
            $urlPath = parse_url($appUrl, PHP_URL_PATH);
            $prefix = $urlPath ? trim($urlPath, '/') : '';
        }
    }

    // Last resort: some hosts/proxies break SCRIPT_NAME / SCRIPT_FILENAME; infer the folder
    // from the URL (e.g. /backend/admin/login → strip "backend" so Filament matches /admin/…).
    if ($prefix === null || $prefix === '') {
        $requestUriProbe = $_SERVER['REQUEST_URI'] ?? '/';
        $pathProbe = parse_url($requestUriProbe, PHP_URL_PATH) ?: '/';
        if (preg_match('#^/([^/]+)/(admin|api|up|livewire|sanctum)(/|$)#i', $pathProbe, $m)) {
            $prefix = $m[1];
        } elseif (preg_match('#^/([^/]+)/livewire-[a-z0-9]+(/|$)#i', $pathProbe, $m)) {
            // Livewire 4: /livewire-{hash}/update, /upload-file, etc.
            $prefix = $m[1];
        }
    }

    if (is_string($prefix) && $prefix !== '') {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $pathPart = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        if ($pathPart === '/'.$prefix || str_starts_with($pathPart, '/'.$prefix.'/')) {
            // Expose /{prefix} as the application base URL instead of removing it from
            // REQUEST_URI. Symfony derives the base path from SCRIPT_NAME (basename matches
            // the real index.php), so the path Laravel routes on still drops the prefix
            // (/admin, /api, /livewire-…) exactly like before , BUT request()->url() keeps
            // the /{prefix} segment. That is what makes signed URLs validate: Livewire signs
            // temporary upload/preview URLs (and any signed route) with
            // URL::forceRootUrl(.../{prefix}) and validation re-hashes request()->url();
            // stripping the prefix made the two diverge → 401 "failed to upload".
            $_SERVER['SCRIPT_NAME'] = '/'.$prefix.'/index.php';
            $_SERVER['PHP_SELF'] = '/'.$prefix.'/index.php';
            // Stale CGI vars can make Symfony mis-derive the path info; clear them.
            unset($_SERVER['PATH_INFO'], $_SERVER['ORIG_PATH_INFO'], $_SERVER['ORIG_SCRIPT_NAME']);
        }
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
