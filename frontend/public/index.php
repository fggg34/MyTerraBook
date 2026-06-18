<?php

/**
 * Storefront entry for shared hosting (public_html/index.php).
 *
 * Renders the SPA shell with server-injected CMS bootstrap. Fail-safe: on ANY error it
 * serves the static index.html so the site never returns a 500 (the SPA then loads its
 * content from the API as a fallback).
 */
define('LARAVEL_START', microtime(true));

$backendRoot = __DIR__.'/backend';
$indexHtml = __DIR__.'/index.html';

$serveStatic = static function () use ($indexHtml): void {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');

    if (is_file($indexHtml)) {
        echo file_get_contents($indexHtml);

        return;
    }

    http_response_code(503);
    echo '<!doctype html><html><body><p>Storefront is temporarily unavailable.</p></body></html>';
};

try {
    if (! is_file($backendRoot.'/vendor/autoload.php')) {
        $serveStatic();

        return;
    }

    if (file_exists($maintenance = $backendRoot.'/storage/framework/maintenance.php')) {
        require $maintenance;
    }

    require $backendRoot.'/vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once $backendRoot.'/bootstrap/app.php';

    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();

    $html = $app->make(App\Services\SpaShellService::class)->renderShell();

    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    echo $html;
} catch (\Throwable $e) {
    $serveStatic();
}
