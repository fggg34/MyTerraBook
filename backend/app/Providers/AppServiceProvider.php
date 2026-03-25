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

        $appUrl = config('app.url');
        if (! $appUrl) {
            return;
        }

        $host = request()->getHost();
        $urlHost = parse_url($appUrl, PHP_URL_HOST);

        if ($urlHost && ($host === $urlHost || $host === 'www.'.$urlHost)) {
            URL::forceRootUrl(rtrim($appUrl, '/'));
        }
    }
}
