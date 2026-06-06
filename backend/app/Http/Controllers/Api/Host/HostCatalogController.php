<?php

namespace App\Http\Controllers\Api\Host;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Characteristic;
use App\Models\GuestHouseAmenity;
use App\Models\Location;
use App\Models\PriceType;
use App\Models\RentalOption;
use App\Models\TaxRate;
use Illuminate\Http\JsonResponse;

class HostCatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        $rows = Category::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']);

        return response()->json(['data' => $rows]);
    }

    public function locations(): JsonResponse
    {
        $rows = Location::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug', 'city']);

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
