<?php

namespace App\Http\Middleware;

use App\Support\DesignerPanelAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictDesignerFilamentAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || DesignerPanelAccess::userCanAccessRequest($user, $request)) {
            return $next($request);
        }

        abort(403, 'This area is restricted to administrators.');
    }
}
