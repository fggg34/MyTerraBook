<?php

namespace App\Http\Controllers;

use App\Services\SpaShellService;
use Illuminate\Http\Response;

class SpaShellController extends Controller
{
    public function show(SpaShellService $spaShell): Response
    {
        return response($spaShell->renderShell(), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }
}
