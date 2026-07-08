<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Carbon;

class AdminCalendarEmbed
{
    public static function embedUrlFor(?User $user): string
    {
        $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        if (! $user) {
            return $frontend.'/admin/calendar';
        }

        $token = $user->createToken(
            'admin-calendar-embed',
            ['admin'],
            Carbon::now()->addMinutes(30),
        )->plainTextToken;

        return $frontend.'/admin/embed/calendar?handoff='.urlencode($token);
    }
}
