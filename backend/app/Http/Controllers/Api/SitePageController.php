<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SitePageResource;
use App\Models\SitePage;
use Illuminate\Http\JsonResponse;

class SitePageController extends Controller
{
    public function show(string $slug): JsonResponse|SitePageResource
    {
        $page = SitePage::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return new SitePageResource($page);
    }
}
