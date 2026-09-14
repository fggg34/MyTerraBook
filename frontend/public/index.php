<?php

require __DIR__.'/block-dot-env.php';

/**
 * Storefront entry for shared hosting (public_html/index.php).
 *
 * Renders the SPA shell with server-injected CMS bootstrap, so the Coming Soon
 * gate and the CMS content both travel inside the first HTML response. Every
 * device then agrees before a single API call goes out.
 *
 * The shell comes from running GET /spa-shell through the Laravel HTTP kernel
 * in this same PHP process: the exact request /backend/spa-shell serves, with
 * a bound request. Booting the kernel on its own is not enough. Providers
 * resolve the URL generator while booting, and that constructor needs a
 * request; without one it throws and every page view used to degrade.
 *
 * Fail-safe: on any error it serves the static index.html so the site never
 * returns a 500. That path is logged, because a silent fallback looks like a
 * working site while actually shipping no content and no gate.
 */
define('LARAVEL_START', microtime(true));

$indexHtml = __DIR__.'/index.html';
$bootstrapMarker = '__MYTERRABOOK_BOOTSTRAP__';

$host = (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '');
$https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'
    || str_contains((string) ($_SERVER['HTTP_CF_VISITOR'] ?? ''), 'https');
$scheme = $https ? 'https' : 'http';
$debug = isset($_GET['__shell_debug']);

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
 * Failure detail is always logged in full. With ?__shell_debug=1 a path-masked,
 * single-line copy also rides on a response header, so a broken boot can be
 * read with curl from anywhere instead of digging through the server logs.
 */
$maskDetail = static function (string $detail): string {
    $masked = str_replace([dirname(__DIR__), __DIR__], '~', $detail);
    $masked = preg_replace('/\s+/', ' ', $masked) ?? $masked;
    $masked = preg_replace('/[^\x20-\x7E]/', '?', $masked) ?? $masked;

    return substr(trim($masked), 0, 240);
};

/**
 * Laravel already renders this exact shell at /backend/spa-shell, and .htaccess
 * never rewrites /backend, so this cannot loop back into this file.
 *
 * Only reached when rendering in process fails. The result is kept in a temp
 * file so a broken deploy cannot turn every page view into a second origin
 * request: a hit is reused for 30 seconds, a miss for 15, whatever the traffic.
 */
$fetchShellOverHttp = static function () use ($host, $scheme, $bootstrapMarker): ?string {
    if ($host === '' || ! function_exists('curl_init')) {
        return null;
    }

    $cacheFile = sys_get_temp_dir().'/myterrabook-shell-'.md5(__DIR__.'|'.$host).'.html';
    if (is_file($cacheFile)) {
        $age = time() - (int) filemtime($cacheFile);
        $cached = (string) file_get_contents($cacheFile);

        // An empty file means "the last attempt failed", so failures are throttled too.
        if ($cached !== '' && $age < 30) {
            return $cached;
        }
        if ($cached === '' && $age < 15) {
            return null;
        }
    }

    $curl = curl_init($scheme.'://'.$host.'/backend/spa-shell');
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 2,
        CURLOPT_CONNECTTIMEOUT => 2,
        CURLOPT_TIMEOUT => 8,
        CURLOPT_ENCODING => '',
        // Cloudflare's browser checks reject requests with no User-Agent, so
        // this request presents the visitor's own.
        CURLOPT_USERAGENT => (string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Mozilla/5.0 (compatible; MyTerraBook shell)'),
        CURLOPT_HTTPHEADER => ['Accept: text/html', 'X-Myterrabook-Shell-Fetch: 1'],
    ]);

    $body = curl_exec($curl);
    $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    // Only a real shell counts. A challenge page or an error page is a miss.
    $html = ($status === 200 && is_string($body) && str_contains($body, $bootstrapMarker)) ? $body : '';
    @file_put_contents($cacheFile, $html, LOCK_EX);

    return $html === '' ? null : $html;
};

/**
 * A storefront with no CMS content and no Coming Soon gate looks like a working
 * site, so every step down is logged and reported in the response header.
 */
$degrade = static function (string $code, string $detail) use ($indexHtml, $sendHtml, $fetchShellOverHttp, $maskDetail, $debug): void {
    error_log('[myterrabook] shell not rendered in process ('.$code.'): '.$detail);

    if ($debug) {
        header('X-Myterrabook-Shell-Detail: '.$maskDetail($detail));
    }

    $remote = $fetchShellOverHttp();
    if ($remote !== null) {
        // Carry the reason even when the fallback rescues the page, otherwise
        // a healthy looking site hides the fact that it is doing two requests.
        $sendHtml($remote, 'http-fallback; reason='.$code);

        return;
    }

    error_log('[myterrabook] serving static index.html: no CMS content and no Coming Soon gate.');

    if (is_file($indexHtml)) {
        $sendHtml((string) file_get_contents($indexHtml), 'static; reason='.$code);

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

/**
 * The request /spa-shell sees in process: the visitor's connection and headers
 * (so proxies, scheme and locale resolve as usual) but not the visitor's path,
 * cookies or credentials. The shell is identical for every visitor and cached
 * by gate state, so nothing personal may reach it.
 *
 * @return array<string, scalar>
 */
$shellServerVars = static function (): array {
    $keep = [
        'REMOTE_ADDR', 'REMOTE_PORT', 'SERVER_ADDR', 'SERVER_NAME', 'SERVER_PORT', 'SERVER_PROTOCOL',
        'SERVER_SOFTWARE', 'DOCUMENT_ROOT', 'REQUEST_TIME', 'REQUEST_TIME_FLOAT',
    ];
    // Credentials stay out; so do validators, which could turn the shell into an empty 304.
    $dropHeaders = ['HTTP_COOKIE', 'HTTP_AUTHORIZATION', 'HTTP_IF_NONE_MATCH', 'HTTP_IF_MODIFIED_SINCE'];
    $server = [];

    foreach ($_SERVER as $key => $value) {
        if (! is_string($key) || ! is_scalar($value)) {
            continue;
        }

        $isHeader = str_starts_with($key, 'HTTP_') && ! in_array($key, $dropHeaders, true);
        if ($isHeader || in_array($key, $keep, true)) {
            $server[$key] = $value;
        }
    }

    return $server;
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

    // The shell has to be built from the index.html next to this file, the one
    // Apache would otherwise serve raw. Dotenv never overrides a variable that
    // is already set, so this wins over .env; a cached config wins over both.
    if (getenv('SPA_INDEX_PATH') === false) {
        putenv('SPA_INDEX_PATH='.$indexHtml);
        $_ENV['SPA_INDEX_PATH'] = $indexHtml;
        $_SERVER['SPA_INDEX_PATH'] = $indexHtml;
    }

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once $backendRoot.'/bootstrap/app.php';

    /** @var \Illuminate\Contracts\Http\Kernel $kernel */
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

    $request = Illuminate\Http\Request::create(
        $scheme.'://'.($host !== '' ? $host : 'localhost').'/spa-shell',
        'GET',
        [],
        [],
        [],
        $shellServerVars(),
    );

    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $html = (string) $response->getContent();

    if ($status === 200 && str_contains($html, $bootstrapMarker)) {
        $sendHtml($html);
        $kernel->terminate($request, $response);

        return;
    }

    if ($status === 200 && str_contains($html, 'id="root"')) {
        // Laravel answered with the built shell but could not assemble the
        // bootstrap payload. It has logged why; the SPA fetches content itself.
        error_log('[myterrabook] shell rendered without bootstrap payload, see storage/logs/laravel.log.');
        $sendHtml($html, 'rendered; reason=no-bootstrap');
        $kernel->terminate($request, $response);

        return;
    }

    $kernel->terminate($request, $response);

    $exception = $response->exception ?? null;
    $degrade(
        'boot-failed',
        $exception instanceof \Throwable
            ? get_class($exception).': '.$exception->getMessage()
            : 'HTTP '.$status.' from /spa-shell with no shell in the body'
    );
} catch (\Throwable $e) {
    $degrade('boot-failed', get_class($e).': '.$e->getMessage());
}
