<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SpaShellService;
use Illuminate\Http\Response;

class AdminCalendarEmbedController extends Controller
{
    public function __invoke(SpaShellService $spaShell): Response
    {
        return response($spaShell->renderShell(), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }
}
