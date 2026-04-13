<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AdminStatsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $totalOrders = Order::query()->count();

        $confirmedRevenueCents = (int) Order::query()
            ->where('order_status', OrderStatus::Confirmed)
            ->sum('total_cents');

        $now = Carbon::now();
        $activeRentals = Order::query()
            ->where('order_status', OrderStatus::Confirmed)
            ->where('pickup_at', '<=', $now)
            ->where('dropoff_at', '>=', $now)
            ->count();

        return response()->json([
            'total_orders' => $totalOrders,
            'total_bookings' => $totalOrders,
            'revenue' => Money::formatDecimalFromCents($confirmedRevenueCents),
            'active_rentals' => $activeRentals,
        ]);
    }
}
