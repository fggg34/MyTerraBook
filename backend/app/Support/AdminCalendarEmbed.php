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

        if (self::shouldServeFromBackend()) {
            return url(self::EMBED_PATH).$query;
        }

        return self::resolveFrontendDevUrl().self::EMBED_PATH.$query;
    }

    /**
     * Production: serve the built SPA shell from Laravel (same origin as Filament).
     * Local dev: use the Vite dev server so /assets/* resolves correctly.
     */
    private static function shouldServeFromBackend(): bool
    {
        if (app()->environment('local')) {
            return false;
        }

        $indexPath = config('spa.index_path');

        return is_string($indexPath) && $indexPath !== '' && File::isFile($indexPath);
    }

    private static function resolveFrontendDevUrl(): string
    {
        $configured = rtrim((string) config('app.frontend_url', ''), '/');

        if ($configured !== '') {
            return $configured;
        }

        return 'http://127.0.0.1:5174';
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
