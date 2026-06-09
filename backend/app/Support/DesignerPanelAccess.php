<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class DesignerPanelAccess
{
    public static function userCanAccessRequest(User $user, Request $request): bool
    {
        return $user->isFullAdmin();
    }
}
