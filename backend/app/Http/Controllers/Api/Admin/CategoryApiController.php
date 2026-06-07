<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MainCategoryResource;
use App\Http\Resources\Api\SubCategoryResource;
use App\Models\MainCategory;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->query('type') === 'main') {
            return response()->json([
                'data' => MainCategoryResource::collection(
                    MainCategory::query()->orderBy('sort_order')->get(),
                ),
            ]);
        }

        return response()->json([
            'data' => SubCategoryResource::collection(
                SubCategory::query()->with('mainCategory')->orderBy('sort_order')->get(),
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if ($request->input('type') === 'main') {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'is_active' => ['boolean'],
            ]);

            $category = MainCategory::query()->create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return response()->json(['data' => new MainCategoryResource($category)], 201);
        }

        $data = $request->validate([
            'main_category_id' => ['required', 'exists:main_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_search_filter' => ['boolean'],
        ]);

        $category = SubCategory::query()->create([
            'main_category_id' => $data['main_category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
            'is_search_filter' => $data['is_search_filter'] ?? true,
        ]);

        return response()->json(['data' => new SubCategoryResource($category->load('mainCategory'))], 201);
    }

    public function show(Request $request, int $category): JsonResponse
    {
        if ($request->query('type') === 'main') {
            $record = MainCategory::query()->findOrFail($category);

            return response()->json(['data' => new MainCategoryResource($record)]);
        }

        $record = SubCategory::query()->with('mainCategory')->findOrFail($category);

        return response()->json(['data' => new SubCategoryResource($record)]);
    }

    public function update(Request $request, int $category): JsonResponse
    {
        if ($request->input('type') === 'main') {
            $record = MainCategory::query()->findOrFail($category);
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'sort_order' => ['nullable', 'integer', 'min:0'],
                'is_active' => ['boolean'],
            ]);
            $record->fill($data);
            $record->save();

            return response()->json(['data' => new MainCategoryResource($record->fresh())]);
        }

        $record = SubCategory::query()->findOrFail($category);
        $data = $request->validate([
            'main_category_id' => ['sometimes', 'exists:main_categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'is_search_filter' => ['boolean'],
        ]);
        $record->fill($data);
        $record->save();

        return response()->json(['data' => new SubCategoryResource($record->fresh()->load('mainCategory'))]);
    }

    public function destroy(Request $request, int $category): JsonResponse
    {
        if ($request->query('type') === 'main') {
            MainCategory::query()->findOrFail($category)->delete();
        } else {
            SubCategory::query()->findOrFail($category)->delete();
        }

        return response()->json(null, 204);
    }
}
