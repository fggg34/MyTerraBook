<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CustomFieldResource;
use App\Models\CustomField;
use Illuminate\Http\JsonResponse;

class CustomFieldController extends Controller
{
    public function index(): JsonResponse
    {
        $fields = CustomField::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        return response()->json([
            'data' => CustomFieldResource::collection($fields),
        ]);
    }
}
