<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CarDetailResource;
use App\Http\Resources\Api\CarListResource;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\LocationResource;
use App\Models\Car;
use App\Models\Category;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\Order;
use App\Models\PriceType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function categories(): JsonResponse
    {
        $rows = Category::query()->where('is_active', true)->orderBy('sort_order')->get();

        return response()->json(['data' => CategoryResource::collection($rows)]);
    }

    public function locations(): JsonResponse
    {
        $rows = Location::query()->where('is_active', true)->orderBy('name')->get();

        return response()->json(['data' => LocationResource::collection($rows)]);
    }

    public function cars(Request $request): JsonResponse
    {
        $pickupId = $request->query('pickup_location_id');
        $dropoffId = $request->query('dropoff_location_id');

        $query = Car::query()
            ->where('is_active', true)
            ->with('category');

        if ($pickupId) {
            $query->whereHas('locations', fn ($q) => $q->where('locations.id', $pickupId)->where('car_location.allows_pickup', true));
        }
        if ($dropoffId) {
            $query->whereHas('locations', fn ($q) => $q->where('locations.id', $dropoffId)->where('car_location.allows_dropoff', true));
        }

        $minPrices = DailyFare::query()
            ->select('car_id', DB::raw('MIN(price_per_day_cents) as min_daily_price_cents'))
            ->groupBy('car_id');

        $cars = $query
            ->leftJoinSub($minPrices, 'min_fares', 'min_fares.car_id', '=', 'cars.id')
            ->select('cars.*')
            ->addSelect('min_fares.min_daily_price_cents')
            ->orderBy('cars.name')
            ->get();

        $rows = $cars->map(fn (Car $car) => [
            'id' => $car->id,
            'name' => $car->name,
            'slug' => $car->slug,
            'category_id' => $car->category_id,
            'transmission' => $car->transmission,
            'fuel_type' => $car->fuel_type,
            'units_available' => $car->units_available,
            'main_image_path' => $car->main_image_path,
            'min_daily_price_cents' => (int) ($car->min_daily_price_cents ?? 0),
        ]);

        return response()->json([
            'data' => CarListResource::collection($rows),
        ]);
    }

    public function car(Car $car): JsonResponse
    {
        $car->load(['category', 'characteristics', 'rentalOptions']);

        $fareMins = DailyFare::query()
            ->where('car_id', $car->id)
            ->selectRaw('price_type_id, MIN(price_per_day_cents) as min_cents')
            ->groupBy('price_type_id')
            ->pluck('min_cents', 'price_type_id');

        $priceTypes = PriceType::query()
            ->whereIn('id', $fareMins->keys())
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (PriceType $pt) => [
                'id' => $pt->id,
                'name' => $pt->name,
                'slug' => $pt->slug,
                'attribute_label' => $pt->attribute_label,
                'attribute_value_per_day' => $pt->attribute_value_per_day,
                'from_price_per_day_cents' => (int) $fareMins->get($pt->id, 0),
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => new CarDetailResource($car, $priceTypes),
        ]);
    }

    public function availabilityCalendar(Car $car): JsonResponse
    {
        $orders = Order::query()
            ->where('car_id', $car->id)
            ->where('order_status', OrderStatus::Confirmed)
            ->select(['id', 'pickup_at', 'dropoff_at'])
            ->orderBy('pickup_at')
            ->get();

        $booked = $orders->map(fn (Order $o) => [
            'id' => $o->id,
            'start' => $o->pickup_at->toIso8601String(),
            'end' => $o->dropoff_at->toIso8601String(),
        ]);

        return response()->json([
            'booked' => $booked,
            'blocked' => [],
        ]);
    }
}
