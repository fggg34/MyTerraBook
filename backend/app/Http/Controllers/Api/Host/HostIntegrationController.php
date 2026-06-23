<?php

namespace App\Http\Controllers\Api\Host;

use App\Http\Controllers\Controller;
use App\Http\Resources\Host\HostIntegrationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostIntegrationController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->ensureIntegrationToken();

        return response()->json([
            'data' => new HostIntegrationResource($user->fresh()),
        ]);
    }

    public function regenerateToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->regenerateIntegrationToken();

        return response()->json([
            'message' => 'Integration token regenerated.',
            'data' => new HostIntegrationResource($user->fresh()),
        ]);
    }
}
