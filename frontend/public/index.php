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

/**
 * The status header carries a short reason code, never a path. It is the only
 * way to tell a healthy shell from a degraded one from outside the server:
 * curl -sI https://myterrabook.com/ | grep -i x-myterrabook-shell
 */
$sendHtml = static function (string $html, string $status = 'rendered'): void {
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('X-Myterrabook-Shell: '.$status);
    echo $html;
};

/**
 * Laravel already renders this exact shell at /backend/spa-shell, and .htaccess
 * never rewrites /backend, so this cannot loop back into this file.
 *
 * Only reached when booting in process fails. The result is kept in a temp file
 * so a broken deploy cannot turn every page view into a second origin request:
 * at worst one request per minute, whatever the traffic. Cost of that is up to
 * a minute of staleness on a path that is already degraded.
 */
$fetchShellOverHttp = static function (): ?string {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '' || ! function_exists('curl_init')) {
        return null;
    }

    // An empty file means "the last attempt failed", so failures are throttled too.
    $cacheFile = sys_get_temp_dir().'/myterrabook-shell-'.md5(__DIR__.'|'.$host).'.html';
    if (is_file($cacheFile) && (time() - (int) filemtime($cacheFile)) < 60) {
        $cached = (string) file_get_contents($cacheFile);

        return $cached === '' ? null : $cached;
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

    $html = ($status === 200 && is_string($body) && $body !== '') ? $body : '';
    @file_put_contents($cacheFile, $html, LOCK_EX);

    return $html === '' ? null : $html;
};

/**
 * A storefront with no CMS content and no Coming Soon gate looks like a working
 * site, so every step down is logged and reported in the response header.
 */
$degrade = static function (string $code, string $detail) use ($indexHtml, $sendHtml, $fetchShellOverHttp): void {
    error_log('[myterrabook] shell not rendered in process ('.$code.'): '.$detail);

    $remote = $fetchShellOverHttp();
    if ($remote !== null) {
        // Carry the reason even when the fallback rescues the page, otherwise
        // a healthy looking site hides the fact that it is doing two requests.
        $sendHtml($remote, 'http-fallback; reason='.$code);

        return;
    }

    error_log('[myterrabook] serving static index.html: no CMS content and no Coming Soon gate.');

    if (is_file($indexHtml)) {
        $sendHtml(file_get_contents($indexHtml), 'static; reason='.$code);

        return;
    }

    http_response_code(503);
    echo '<!doctype html><html><body><p>Storefront is temporarily unavailable.</p></body></html>';
};

/**
 * The Laravel app root sits in a different place on every host, and a deploy
 * that syncs dist/ over public_html can move or remove public_html/backend. So
 * search the plausible layouts rather than assuming one: this entry point going
 * blind is invisible from the outside, the site just quietly loses its content.
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

    // Walk up from public_html and try the usual names at each level, so the
    // app root can be a sibling, an uncle, or the account home itself.
    $level = __DIR__;
    for ($depth = 0; $depth < 3; $depth++) {
        $level = dirname($level);
        if ($level === '' || $level === '/' || $level === '.') {
            break;
        }

        $candidates[] = $level;
        foreach (['backend', 'laravel', 'api', 'myterrabook'] as $name) {
            $candidates[] = $level.'/'.$name;
        }
    }

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
        $degrade(
            'app-root-not-found',
            'Set MYTERRABOOK_BACKEND_ROOT to the directory holding vendor/ and bootstrap/. Tried: '
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
    $degrade('boot-failed', $e->getMessage());
}
