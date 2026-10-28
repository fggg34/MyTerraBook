<?php

namespace App\Providers;

use App\Http\Responses\FilamentLoginResponse;
use App\Models\BlogPost;
use App\Models\BookingChangeRequest;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Observers\BlogPostObserver;
use App\Observers\GuestHouseBookingObserver;
use App\Observers\OrderObserver;
use App\Observers\SiteContentListingStatsObserver;
use App\Policies\BookingChangeRequestPolicy;
use App\Policies\CarPolicy;
use App\Policies\GuestHouseBookingPolicy;
use App\Policies\GuestHousePolicy;
use App\Policies\OrderPolicy;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(GuestHouse::class, GuestHousePolicy::class);
        Gate::policy(Car::class, CarPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(GuestHouseBooking::class, GuestHouseBookingPolicy::class);
        Gate::policy(BookingChangeRequest::class, BookingChangeRequestPolicy::class);

        Order::observe(OrderObserver::class);
        GuestHouseBooking::observe(GuestHouseBookingObserver::class);
        BlogPost::observe(BlogPostObserver::class);

        $listingStatsObserver = SiteContentListingStatsObserver::class;
        Car::observe($listingStatsObserver);
        DailyFare::observe($listingStatsObserver);
        GuestHouse::observe($listingStatsObserver);

        $this->app->bind(FilamentLoginResponseContract::class, FilamentLoginResponse::class);

        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $frontend = rtrim((string) config('app.frontend_url'), '/');
            $email = urlencode($notifiable->getEmailForPasswordReset());
            $intent = method_exists($notifiable, 'isHost') && $notifiable->isHost() ? '&intent=host' : '';

            return "{$frontend}/reset-password?token={$token}&email={$email}{$intent}";
        });

        if ($this->app->runningInConsole()) {
            return;
        }

        $this->forgetStaleAdminViews();

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

    /**
     * Production PHP can keep the previous compiled admin templates after a pull.
     * Drop them once when the shell view changes so the next response matches this code.
     */
    private function forgetStaleAdminViews(): void
    {
        $blade = resource_path('views/filament/panel/admin-redesign-shell.blade.php');
        $mtime = @filemtime($blade);
        if (! is_int($mtime)) {
            return;
        }

        $flag = storage_path('framework/cache/admin-shell-mtime');
        $seen = is_file($flag) ? trim((string) @file_get_contents($flag)) : '';
        if ($seen === (string) $mtime) {
            return;
        }

        $views = storage_path('framework/views');
        foreach (glob($views.DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            if (! is_file($file)) {
                continue;
            }
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($file, true);
            }
            @unlink($file);
        }

        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }

        @file_put_contents($flag, (string) $mtime);
    }
}
