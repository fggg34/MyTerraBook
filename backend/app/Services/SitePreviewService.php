<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class SitePreviewService
{
    /**
     * Whether the public storefront should be unlocked (not showing Coming Soon).
     */
    public function isUnlocked(?Request $request = null): bool
    {
        // Explicit env override: force the storefront open (e.g. local testing).
        if (config('app.site_preview_force_open')) {
            return true;
        }

        if (! $this->isComingSoonEnabled()) {
            return true;
        }

        $request ??= request();

        $webUser = auth()->guard('web')->user();
        if ($webUser instanceof User && $webUser->canPreviewSite()) {
            return true;
        }

        $token = $request->bearerToken();
        if ($token) {
            $accessToken = PersonalAccessToken::findToken($token);
            $sanctumUser = $accessToken?->tokenable;
            if ($sanctumUser instanceof User && $sanctumUser->canPreviewSite()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gate state for the HTML shell and /api/bootstrap. Session free, so it
     * describes what an anonymous visitor must see. Admins and designers are
     * unlocked afterwards by the SPA calling /api/site-preview with their
     * session or token.
     *
     * @return array{comingSoon: bool, guestUnlocked: bool}
     */
    public function publicState(): array
    {
        $comingSoon = $this->isComingSoonEnabled();

        return [
            'comingSoon' => $comingSoon,
            'guestUnlocked' => ! $comingSoon || (bool) config('app.site_preview_force_open'),
        ];
    }

    public function isComingSoonEnabled(): bool
    {
        $enabled = data_get(
            Setting::getValue('system.coming_soon', ['enabled' => false]),
            'enabled',
            false,
        );

        return filter_var($enabled, FILTER_VALIDATE_BOOLEAN);
    }
}
