<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Filament\Pages\Dashboard;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

/**
 * Default Filament login uses redirect()->intended(Filament::getUrl()) which can:
 * - follow a stale session "intended" URL without the /backend prefix (404 on the SPA host)
 * - use url('admin') when the home route name does not match, which may miss the subdirectory root.
 */
class FilamentLoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->to(route(Dashboard::getRouteName()));
    }
}
