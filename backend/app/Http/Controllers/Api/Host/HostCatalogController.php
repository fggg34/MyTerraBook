<?php

namespace App\Http\Controllers\Api\Host;

use App\Http\Controllers\Controller;
use App\Models\Characteristic;
use App\Models\GuestHouseAmenity;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\PriceType;
use App\Models\RentalOption;
use App\Models\SubCategory;
use App\Models\TaxRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostCatalogController extends Controller
{
    public function categories(Request $request): JsonResponse
    {
        $query = SubCategory::query()
            ->with('mainCategory')
            ->where('is_active', true)
            ->whereHas('mainCategory', fn ($builder) => $builder->where('is_active', true))
            ->orderBy('sort_order');

        if ($request->filled('main_category')) {
            $query->whereHas('mainCategory', fn ($builder) => $builder->where('slug', $request->query('main_category')));
        }

        $rows = $query->get(['id', 'main_category_id', 'name', 'slug']);

        return response()->json(['data' => $rows]);
    }

    public function mainCategories(): JsonResponse
    {
        $rows = MainCategory::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']);

        return response()->json(['data' => $rows]);
    }

    public function locations(): JsonResponse
    {
        $rows = Location::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug']);

        return response()->json(['data' => $rows]);
    }

    public function characteristics(): JsonResponse
    {
        $rows = Characteristic::query()->orderBy('sort_order')->get(['id', 'name', 'slug', 'icon']);

        return response()->json(['data' => $rows]);
    }

    public function rentalOptions(): JsonResponse
    {
        $rows = RentalOption::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']);

        return response()->json(['data' => $rows]);
    }

    public function priceTypes(): JsonResponse
    {
        $rows = PriceType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug', 'attribute_label']);

        return response()->json(['data' => $rows]);
    }

    public function amenities(): JsonResponse
    {
        $rows = GuestHouseAmenity::query()->orderBy('group')->orderBy('name')->get(['id', 'name', 'icon', 'group']);

        return response()->json(['data' => $rows]);
    }

    public function taxRates(): JsonResponse
    {
        $rows = TaxRate::query()->orderBy('name')->get(['id', 'name', 'rate_bips']);

        return response()->json(['data' => $rows]);
    }
}
