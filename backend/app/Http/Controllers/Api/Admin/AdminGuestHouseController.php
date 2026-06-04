<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GuestHouseListResource;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminGuestHouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $houses = GuestHouse::query()
            ->withCount([
                'bookings',
                'bookings as confirmed_bookings_count' => fn ($q) => $q->where('status', 'confirmed'),
            ])
            ->orderBy('name')
            ->paginate((int) $request->query('per_page', 20));

        $data = $houses->getCollection()->map(function (GuestHouse $house) {
            return array_merge(
                (new GuestHouseListResource($house))->resolve(),
                [
                    'status' => $house->status->value,
                    'bookings_count' => (int) ($house->bookings_count ?? 0),
                    'confirmed_bookings_count' => (int) ($house->confirmed_bookings_count ?? 0),
                ],
            );
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $houses->currentPage(),
                'last_page' => $houses->lastPage(),
                'total' => $houses->total(),
            ],
        ]);
    }

    public function stats(): JsonResponse
    {
        $totalHouses = GuestHouse::query()->count();
        $totalBookings = GuestHouseBooking::query()->count();

        $revenueThisMonth = (int) GuestHouseBooking::query()
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');

        $confirmedNights = (int) GuestHouseBooking::query()
            ->where('status', 'confirmed')
            ->whereMonth('check_in', now()->month)
            ->sum('nights');

        $daysInMonth = now()->daysInMonth;
        $activeHouses = max(1, GuestHouse::query()->where('status', 'active')->count());
        $occupancyRate = min(100, round(($confirmedNights / ($daysInMonth * $activeHouses)) * 100, 1));

        return response()->json([
            'data' => [
                'total_houses' => $totalHouses,
                'total_bookings' => $totalBookings,
                'revenue_this_month_cents' => $revenueThisMonth,
                'occupancy_rate' => $occupancyRate,
            ],
        ]);
    }
}
