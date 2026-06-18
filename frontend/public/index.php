<?php

/**
 * Storefront entry for shared hosting (public_html/index.php).
 *
 * Renders the SPA shell directly — no HTTP kernel / session middleware — so admin
 * Filament sessions in the same browser are not overwritten.
 */
define('LARAVEL_START', microtime(true));

$backendRoot = __DIR__.'/backend';

if (! is_file($backendRoot.'/vendor/autoload.php')) {
    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html><body><p>Storefront is temporarily unavailable.</p></body></html>';
    exit;
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
