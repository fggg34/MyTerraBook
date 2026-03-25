<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CarResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\LocationResource;
use App\Models\Booking;
use App\Models\Car;
use App\Models\CarUnavailability;
use App\Models\Category;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function categories()
    {
        return CategoryResource::collection(Category::query()->active()->orderBy('sort_order')->get());
    }

    public function locations()
    {
        return LocationResource::collection(Location::query()->active()->orderBy('sort_order')->get());
    }

    public function cars(Request $request)
    {
        $query = Car::query()->active()->with(['category', 'images']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        return CarResource::collection($query->paginate(12));
    }

    public function car(Car $car)
    {
        return CarResource::make($car->load(['category', 'images']));
    }

    public function availabilityCalendar(Car $car, Request $request)
    {
        $from = Carbon::parse($request->input('from', now()->toDateString()))->startOfDay();
        $to = Carbon::parse($request->input('to', now()->addDays(60)->toDateString()))->endOfDay();

        $bookedRanges = Booking::query()
            ->where('car_id', $car->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($from, $to) {
                $query
                    ->whereBetween('pickup_at', [$from, $to])
                    ->orWhereBetween('dropoff_at', [$from, $to])
                    ->orWhere(function ($nested) use ($from, $to) {
                        $nested
                            ->where('pickup_at', '<=', $from)
                            ->where('dropoff_at', '>=', $to);
                    });
            })
            ->orderBy('pickup_at')
            ->get(['id', 'pickup_at as start', 'dropoff_at as end', 'status']);

        $blockedRanges = CarUnavailability::query()
            ->where('car_id', $car->id)
            ->where(function ($query) use ($from, $to) {
                $query
                    ->whereBetween('starts_at', [$from, $to])
                    ->orWhereBetween('ends_at', [$from, $to])
                    ->orWhere(function ($nested) use ($from, $to) {
                        $nested
                            ->where('starts_at', '<=', $from)
                            ->where('ends_at', '>=', $to);
                    });
            })
            ->orderBy('starts_at')
            ->get(['id', 'starts_at as start', 'ends_at as end', 'reason']);

        return response()->json([
            'car_id' => $car->id,
            'from' => $from->toDateTimeString(),
            'to' => $to->toDateTimeString(),
            'booked' => $bookedRanges,
            'blocked' => $blockedRanges,
        ]);
    }
}
