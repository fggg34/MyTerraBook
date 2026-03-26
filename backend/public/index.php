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
 * Strip URL prefix (e.g. /backend) from REQUEST_URI so routes match (/admin, /api/…).
 * Live servers often omit the path in APP_URL or cache a wrong .env — prefer SCRIPT_NAME.
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
        }
    }

    if (is_string($prefix) && $prefix !== '') {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $pathPart = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        if ($pathPart === '/'.$prefix || str_starts_with($pathPart, '/'.$prefix.'/')) {
            $trimmed = substr($pathPart, strlen($prefix) + 1);
            $newPath = ($trimmed === '' || $trimmed === false) ? '/' : '/'.$trimmed;
            $query = parse_url($requestUri, PHP_URL_QUERY);
            $_SERVER['REQUEST_URI'] = $newPath.($query !== null && $query !== '' ? '?'.$query : '');
        }
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
