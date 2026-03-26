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

// When Laravel is deployed under a URL prefix (e.g. APP_URL=https://domain.com/backend),
// strip that prefix from REQUEST_URI before routing. Otherwise paths look like
// /backend/admin and no route matches (/admin is registered), causing 404.
if (is_file(__DIR__.'/../.env')) {
    Dotenv::createImmutable(__DIR__.'/..')->safeLoad();
}
$appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL');
if (is_string($appUrl) && $appUrl !== '') {
    $urlPath = parse_url($appUrl, PHP_URL_PATH);
    $prefix = $urlPath ? trim($urlPath, '/') : '';
    if ($prefix !== '') {
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
