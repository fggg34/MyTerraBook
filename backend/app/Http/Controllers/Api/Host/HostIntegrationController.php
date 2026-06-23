<?php

namespace App\Http\Controllers\Api\Host;

use App\Http\Controllers\Controller;
use App\Http\Resources\Host\HostCarIntegrationResource;
use App\Models\Car;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostIntegrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cars = Car::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        foreach ($cars as $car) {
            $car->ensureIntegrationToken();
        }

        return response()->json([
            'data' => HostCarIntegrationResource::collection($cars->fresh()),
        ]);
    }

    public function regenerateToken(Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $car->regenerateIntegrationToken();

        return response()->json([
            'message' => 'Integration token regenerated.',
            'data' => new HostCarIntegrationResource($car->fresh()),
        ]);
    }
}
