<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiteContentService;
use Illuminate\Http\JsonResponse;

class HomepageController extends Controller
{
    public function show(SiteContentService $siteContent): JsonResponse
    {
        return response()->json($siteContent->homepagePayload());
    }
}
