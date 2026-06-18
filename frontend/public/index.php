<?php

/**
 * Storefront entry for shared hosting (public_html/index.php).
 *
 * Some hosts use DirectoryIndex index.php before index.html. Without this file, an old
 * index.php that bootstraps Laravel sends visitors to the admin login instead of the SPA.
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

$request = Illuminate\Http\Request::create(
    '/spa-shell',
    'GET',
    server: [
        'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'localhost',
        'HTTPS' => (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'on' : 'off',
        'SERVER_NAME' => $_SERVER['SERVER_NAME'] ?? 'localhost',
        'REQUEST_URI' => '/spa-shell',
    ],
);

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
