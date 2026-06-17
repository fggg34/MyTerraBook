<?php

namespace App\Http\Controllers;

use App\Services\Admin\AdminBrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FaviconController extends Controller
{
    public function show(): Response|RedirectResponse|BinaryFileResponse
    {
        $url = app(AdminBrandingService::class)->faviconUrl();

        if (! is_string($url) || $url === '') {
            return response('', 204);
        }

        if (str_contains($url, '/storage/')) {
            $path = Str::after($url, '/storage/');

            if (Storage::disk('public')->exists($path)) {
                return response()->file(Storage::disk('public')->path($path), [
                    'Cache-Control' => 'public, max-age=3600',
                ]);
            }
        }

        return redirect()->to($url);
    }
}
