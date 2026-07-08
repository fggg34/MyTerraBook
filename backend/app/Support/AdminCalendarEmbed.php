<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

class AdminCalendarEmbed
{
    public const EMBED_PATH = '/calendar-embed';

    public static function embedUrlFor(?User $user): string
    {
        $handoff = self::handoffTokenFor($user);
        $query = $handoff !== null ? '?handoff='.urlencode($handoff) : '';

        if (app()->environment('local')) {
            return self::resolveFrontendDevUrl().self::EMBED_PATH.$query;
        }

        if (self::canServeBackendShell()) {
            return url(self::EMBED_PATH).$query;
        }

        return self::resolvePublicFrontendUrl().self::EMBED_PATH.$query;
    }

    public static function resolvePublicFrontendUrl(): string
    {
        $configured = rtrim((string) config('app.frontend_url', ''), '/');

        if (self::isUsablePublicUrl($configured)) {
            return $configured;
        }

        $appUrl = rtrim((string) config('app.url', ''), '/');

        if ($appUrl !== '' && str_ends_with($appUrl, '/backend')) {
            return substr($appUrl, 0, -strlen('/backend'));
        }

        if ($appUrl !== '' && ! str_contains($appUrl, '/backend')) {
            return $appUrl;
        }

        return 'https://myterrabook.com';
    }

    public static function canServeBackendShell(): bool
    {
        $indexPath = config('spa.index_path');

        return is_string($indexPath) && $indexPath !== '' && File::isFile($indexPath);
    }

    private static function isUsablePublicUrl(string $url): bool
    {
        if ($url === '') {
            return false;
        }

        if (preg_match('#^https?://(127\.0\.0\.1|localhost)(:\d+)?$#i', $url)) {
            return false;
        }

        return true;
    }

    private static function resolveFrontendDevUrl(): string
    {
        $configured = rtrim((string) config('app.frontend_url', ''), '/');

        if ($configured !== '') {
            return $configured;
        }

        return 'http://127.0.0.1:5174';
    }

    public static function createHandoffToken(?User $user): ?string
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

    private static function handoffTokenFor(?User $user): ?string
    {
        return self::createHandoffToken($user);
    }
}
