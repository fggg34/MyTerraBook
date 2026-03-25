<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalBookings = Booking::query()->count();
        $revenue = Booking::query()
            ->where('status', BookingStatus::Confirmed)
            ->sum('total');
        $activeRentals = Booking::query()
            ->where('status', BookingStatus::Confirmed)
            ->where('pickup_at', '<=', Carbon::now())
            ->where('dropoff_at', '>=', Carbon::now())
            ->count();

        return response()->json([
            'total_bookings' => $totalBookings,
            'revenue' => (float) $revenue,
            'active_rentals' => $activeRentals,
        ]);
    }
}
