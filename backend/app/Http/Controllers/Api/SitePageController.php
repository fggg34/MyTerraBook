<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiteContentService;
use Illuminate\Http\JsonResponse;

class SitePageController extends Controller
{
    public function show(string $slug, SiteContentService $siteContent): JsonResponse
    {
        $data = $siteContent->sitePageApiPayload($slug);

        if ($data === null) {
            abort(404);
        }

        return response()->json(['data' => $data]);
    }
}
