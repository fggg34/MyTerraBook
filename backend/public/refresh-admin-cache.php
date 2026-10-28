<?php

/**
 * One-shot after deploy: https://myterrabook.com/backend/refresh-admin-cache.php?k=tb-admin-refresh
 * Clears compiled Blade views and PHP opcache so the admin matches the pulled code.
 */
if (($_GET['k'] ?? '') !== 'tb-admin-refresh') {
    http_response_code(404);
    exit;
}

header('Content-Type: text/plain; charset=utf-8');

$views = dirname(__DIR__).'/storage/framework/views';
$cleared = 0;
foreach (glob($views.'/*') ?: [] as $file) {
    if (! is_file($file)) {
        continue;
    }
    if (function_exists('opcache_invalidate')) {
        @opcache_invalidate($file, true);
    }
    if (@unlink($file)) {
        $cleared++;
    }
}

$reset = function_exists('opcache_reset') ? @opcache_reset() : false;

echo "cleared_views={$cleared}\n";
echo 'opcache_reset='.($reset ? 'yes' : 'no')."\n";
echo 'php='.PHP_VERSION."\n";
