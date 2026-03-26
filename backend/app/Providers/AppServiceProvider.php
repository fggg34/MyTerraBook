<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $appUrl = (string) config('app.url');
        if ($appUrl === '') {
            return;
        }

        $explicitPrefix = env('LARAVEL_URL_PREFIX');
        $explicitPrefix = is_string($explicitPrefix) && trim($explicitPrefix) !== '' ? trim($explicitPrefix, '/') : '';
        $pathFromAppUrl = parse_url($appUrl, PHP_URL_PATH);
        $pathTrim = is_string($pathFromAppUrl) ? trim($pathFromAppUrl, '/') : '';

        // If APP_URL is only the domain but the app is served under /backend, redirects (e.g. after Filament login) would 404 at /admin.
        if ($explicitPrefix !== '' && $pathTrim === '') {
            $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
            $urlHostPart = parse_url($appUrl, PHP_URL_HOST);
            $port = parse_url($appUrl, PHP_URL_PORT);
            if (is_string($urlHostPart) && $urlHostPart !== '') {
                $authority = $urlHostPart.($port ? ':'.$port : '');
                $appUrl = $scheme.'://'.$authority.'/'.$explicitPrefix;
            }
        }

        $host = request()->getHost();
        $urlHost = parse_url($appUrl, PHP_URL_HOST);
        $stripWww = static fn (string $h): string => (string) preg_replace('/^www\./i', '', $h);

        if ($urlHost && $stripWww($host) === $stripWww($urlHost)) {
            URL::forceRootUrl(rtrim($appUrl, '/'));
        }
    }
}
