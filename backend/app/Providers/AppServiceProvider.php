<?php

namespace App\Providers;

use App\Http\Responses\FilamentLoginResponse;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
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
        $this->app->bind(FilamentLoginResponseContract::class, FilamentLoginResponse::class);

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

        $effectiveRoot = rtrim($appUrl, '/');
        $urlPathOnly = parse_url($effectiveRoot, PHP_URL_PATH);
        $appPathSegment = is_string($urlPathOnly) ? trim($urlPathOnly, '/') : '';

        // Livewire passes paths like /backend/livewire-…/update into url() while forceRootUrl already
        // ends with /backend → /backend/backend/… without this strip.
        if ($appPathSegment !== '') {
            URL::formatPathUsing(function (string $path) use ($appPathSegment): string {
                $prefix = '/'.$appPathSegment;
                if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                    $rest = substr($path, strlen($prefix));

                    return '/'.ltrim($rest, '/');
                }

                return $path;
            });
        }

        // Always set root URL for web (not only when Host matches APP_URL): behind proxies or
        // mismatched www, request()->root() can omit /backend and url()/redirects 404 at the domain root.
        URL::forceRootUrl($effectiveRoot);
    }
}
