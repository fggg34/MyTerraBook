<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SpaShellService;
use App\Support\AdminCalendarEmbed;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class AdminCalendarEmbedController extends Controller
{
    public function __invoke(SpaShellService $spaShell): Response
    {
        $indexPath = config('spa.index_path');

        if (! is_string($indexPath) || $indexPath === '' || ! File::isFile($indexPath)) {
            return response($this->missingShellHtml(), 503, [
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Frame-Options' => 'SAMEORIGIN',
            ]);
        }

        return response($spaShell->renderShell(), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    private function missingShellHtml(): string
    {
        $publicUrl = AdminCalendarEmbed::resolvePublicFrontendUrl().AdminCalendarEmbed::EMBED_PATH;
        $indexPath = (string) config('spa.index_path');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Calendar setup required</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 1.5rem; color: #334155; line-height: 1.5; }
        code { background: #f1f5f9; padding: 0.1rem 0.35rem; border-radius: 0.25rem; }
    </style>
</head>
<body>
    <p><strong>Admin calendar shell is not configured on the server.</strong></p>
    <p>Set <code>SPA_INDEX_PATH</code> in <code>backend/.env</code> to your built storefront
    <code>index.html</code> (for example <code>/home/myterra/public_html/index.html</code>),
    then run <code>php artisan config:clear</code>.</p>
    <p>Current value: <code>{$indexPath}</code></p>
    <p>Until that is set, the Filament calendar should load the public app at
    <a href="{$publicUrl}">{$publicUrl}</a> instead.</p>
</body>
</html>
HTML;
    }
}
