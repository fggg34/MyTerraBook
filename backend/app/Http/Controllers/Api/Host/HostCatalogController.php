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
        $rows = Location::query()
            ->with(['schedules:id,location_id,weekday,opening_time,closing_time,is_closed'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'default_opening_time',
                'default_closing_time',
                'suggested_preselected_time',
            ])
            ->map(fn (Location $location): array => [
                'id' => $location->id,
                'name' => $location->name,
                'slug' => $location->slug,
                'default_opening_time' => self::formatTime($location->default_opening_time),
                'default_closing_time' => self::formatTime($location->default_closing_time),
                'suggested_preselected_time' => self::formatTime($location->suggested_preselected_time),
                'schedules' => $location->schedules->map(fn ($schedule): array => [
                    'weekday' => $schedule->weekday,
                    'opening_time' => self::formatTime($schedule->opening_time),
                    'closing_time' => self::formatTime($schedule->closing_time),
                    'is_closed' => (bool) $schedule->is_closed,
                ])->values()->all(),
            ]);

        return response()->json(['data' => $rows]);
    }

    private static function formatTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $str = (string) $value;
        if (preg_match('/^(\d{1,2}):(\d{2})/', $str, $m)) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        return null;
    }

    public function characteristics(): JsonResponse
    {
        $columns = ['id', 'name', 'slug', 'icon_path'];
        if (\Illuminate\Support\Facades\Schema::hasColumn('characteristics', 'group')) {
            $columns[] = 'group';
        }

        $rows = Characteristic::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get($columns);

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
        $rows = TaxRate::query()->orderBy('name')->get(['id', 'name', 'basis_points']);

        return response()->json(['data' => $rows]);
    }
}
