<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;

class AdminCalendarEmbed
{
    public static function embedUrlFor(?User $user): string
    {
        $frontend = self::resolveFrontendUrl();
        $handoff = self::handoffTokenFor($user);

        if ($handoff === null) {
            return $frontend.'/admin/embed/calendar';
        }

        return $frontend.'/admin/embed/calendar?handoff='.urlencode($handoff);
    }

    public static function resolveFrontendUrl(): string
    {
        $configured = rtrim((string) config('app.frontend_url', ''), '/');

        if (self::isUsableFrontendUrl($configured)) {
            return $configured;
        }

        $appUrl = rtrim((string) config('app.url', ''), '/');

        if ($appUrl !== '' && str_ends_with($appUrl, '/backend')) {
            return substr($appUrl, 0, -strlen('/backend'));
        }

        if ($appUrl !== '') {
            return $appUrl;
        }

        return 'http://127.0.0.1:5174';
    }

    private static function isUsableFrontendUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        if (app()->environment('production') && preg_match('#^https?://(127\.0\.0\.1|localhost)(:\d+)?$#i', $url)) {
            return false;
        }

        return true;
    }

    private static function handoffTokenFor(?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        return $user->createToken(
            'admin-calendar-embed',
            ['admin'],
            Carbon::now()->addMinutes(30),
        )->plainTextToken;
    }
}
