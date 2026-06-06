<?php

namespace App\Http\Controllers\Api\Host;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseStatus;
use App\Enums\ListingApprovalStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HostDashboardController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $carIds = Car::query()->where('user_id', $user->id)->pluck('id');
        $guestHouseIds = GuestHouse::query()->where('user_id', $user->id)->pluck('id');

        $pendingCarBookings = Order::query()
            ->whereIn('car_id', $carIds)
            ->where('order_status', OrderStatus::Pending)
            ->count();

        $pendingGuestBookings = GuestHouseBooking::query()
            ->whereIn('guest_house_id', $guestHouseIds)
            ->where('status', GuestHouseBookingStatus::Pending)
            ->count();

        return response()->json([
            'data' => [
                'guest_houses' => [
                    'draft' => GuestHouse::query()->where('user_id', $user->id)->where('status', GuestHouseStatus::Draft)->count(),
                    'pending_review' => GuestHouse::query()->where('user_id', $user->id)->where('status', GuestHouseStatus::PendingReview)->count(),
                    'live' => GuestHouse::query()->where('user_id', $user->id)->where('status', GuestHouseStatus::Active)->count(),
                    'rejected' => GuestHouse::query()->where('user_id', $user->id)->where('status', GuestHouseStatus::Rejected)->count(),
                ],
                'cars' => [
                    'draft' => Car::query()->where('user_id', $user->id)->where('listing_status', ListingApprovalStatus::Draft)->count(),
                    'pending_review' => Car::query()->where('user_id', $user->id)->where('listing_status', ListingApprovalStatus::PendingReview)->count(),
                    'live' => Car::query()->where('user_id', $user->id)->where('listing_status', ListingApprovalStatus::Approved)->where('is_active', true)->count(),
                    'rejected' => Car::query()->where('user_id', $user->id)->where('listing_status', ListingApprovalStatus::Rejected)->count(),
                ],
                'bookings' => [
                    'pending_car_orders' => $pendingCarBookings,
                    'pending_guesthouse_bookings' => $pendingGuestBookings,
                ],
                'revenue_cents' => [
                    'car_orders' => (int) Order::query()->whereIn('car_id', $carIds)->where('order_status', OrderStatus::Confirmed)->sum('total_cents'),
                    'guesthouse_bookings' => (int) GuestHouseBooking::query()->whereIn('guest_house_id', $guestHouseIds)->where('status', GuestHouseBookingStatus::Confirmed)->sum('total_amount'),
                ],
            ],
        ]);
    }
}
