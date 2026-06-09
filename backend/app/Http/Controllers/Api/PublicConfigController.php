<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PublicShopConfigService;
use Illuminate\Http\JsonResponse;

class PublicConfigController extends Controller
{
    public function show(PublicShopConfigService $config): JsonResponse
    {
        return response()->json($config->payload());
    }
}
