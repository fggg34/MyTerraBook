<?php

namespace App\Http\Controllers\Api;

use App\Data\SiteContentDefaults;
use App\Http\Controllers\Controller;
use App\Models\SiteContentPage;
use App\Services\SiteContentService;
use App\Support\ResolvesPublicStorageUrls;
use Illuminate\Http\JsonResponse;

class SiteContentController extends Controller
{
    use ResolvesPublicStorageUrls;

    public function index(): JsonResponse
    {
        $payload = app(SiteContentService::class)->allPagesCached();

        return response()
            ->json(['data' => $payload])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }

    public function show(string $pageKey): JsonResponse
    {
        if (! config("site_content.pages.{$pageKey}") && ! SiteContentDefaults::forPage($pageKey)) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        $page = SiteContentPage::query()
            ->where('page_key', $pageKey)
            ->where('is_published', true)
            ->first();

        $content = app(SiteContentService::class)->pageContent($pageKey);

        return response()
            ->json(['data' => ['page_key' => $pageKey, 'content' => $content]])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
