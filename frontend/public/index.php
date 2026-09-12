<?php

require __DIR__.'/block-dot-env.php';

/**
 * Storefront entry for shared hosting (public_html/index.php).
 *
 * Renders the SPA shell with server-injected CMS bootstrap, so the Coming Soon
 * gate and the CMS content both travel inside the first HTML response. Every
 * device then agrees before a single API call goes out.
 *
 * Fail-safe: on any error it serves the static index.html so the site never
 * returns a 500. That path is logged, because a silent fallback looks like a
 * working site while actually shipping no content and no gate.
 */
define('LARAVEL_START', microtime(true));

$indexHtml = __DIR__.'/index.html';

$sendHtml = static function (string $html): void {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    echo $html;
};

/**
 * Last resort before the empty static shell: ask Laravel over HTTP. Slower than
 * booting in process, but it still ships CMS content and the Coming Soon gate.
 * .htaccess never rewrites /backend, so this cannot loop back into this file.
 */
$fetchShellOverHttp = static function (): ?string {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '' || ! function_exists('curl_init')) {
        return null;
    }

    $https = ($_SERVER['HTTPS'] ?? 'off') !== 'off' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    $curl = curl_init(($https ? 'https' : 'http').'://'.$host.'/backend/spa-shell');
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT => 6,
    ]);

    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    return ($status === 200 && is_string($body) && $body !== '') ? $body : null;
};

$serveStatic = static function (string $reason) use ($indexHtml, $sendHtml, $fetchShellOverHttp): void {
    error_log('[myterrabook] storefront shell could not be rendered in process: '.$reason);

    $remote = $fetchShellOverHttp();
    if ($remote !== null) {
        $sendHtml($remote);

        return;
    }

    error_log('[myterrabook] serving static index.html with no CMS content and no Coming Soon gate.');

    if (is_file($indexHtml)) {
        $sendHtml(file_get_contents($indexHtml));

        return;
    }

    http_response_code(503);
    echo '<!doctype html><html><body><p>Storefront is temporarily unavailable.</p></body></html>';
};

/**
 * The Laravel app root sits in a different place on every host. public_html/backend
 * is often only the framework public directory (or a symlink to it), so try the
 * usual layouts instead of assuming one.
 *
 * @return list<string>
 */
$backendRootCandidates = static function (): array {
    $candidates = [];

    $fromEnv = getenv('MYTERRABOOK_BACKEND_ROOT');
    if (is_string($fromEnv) && $fromEnv !== '') {
        $candidates[] = $fromEnv;
    }

    // Whole Laravel app inside public_html.
    $candidates[] = __DIR__.'/backend';

    // public_html/backend is the framework public dir, real or symlinked.
    $linked = realpath(__DIR__.'/backend');
    if ($linked !== false) {
        $candidates[] = dirname($linked);
    }

    // App root as a sibling of public_html.
    $candidates[] = dirname(__DIR__).'/backend';
    $candidates[] = dirname(__DIR__).'/laravel';

    return array_values(array_unique(array_filter(array_map(
        static fn (string $path): string => rtrim($path, '/'),
        $candidates,
    ))));
};

try {
    $tried = $backendRootCandidates();
    $backendRoot = null;

    foreach ($tried as $candidate) {
        if (is_file($candidate.'/vendor/autoload.php') && is_file($candidate.'/bootstrap/app.php')) {
            $backendRoot = $candidate;
            break;
        }
    }

    if ($backendRoot === null) {
        $serveStatic(
            'Laravel app root not found. Set MYTERRABOOK_BACKEND_ROOT to the directory holding vendor/ and bootstrap/. Tried: '
            .implode(', ', $tried)
        );

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

    $sendHtml($app->make(App\Services\SpaShellService::class)->renderShell());
} catch (\Throwable $e) {
    $serveStatic($e->getMessage());
}
