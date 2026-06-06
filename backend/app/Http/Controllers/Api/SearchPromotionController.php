<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SearchPromotionResource;
use App\Models\SearchPromotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchPromotionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $context = $request->string('context', 'all')->toString();

        $promotions = SearchPromotion::query()
            ->active()
            ->forContext($context)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => SearchPromotionResource::collection($promotions),
        ]);
    }
}
