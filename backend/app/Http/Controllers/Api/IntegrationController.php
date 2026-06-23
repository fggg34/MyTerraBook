<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\Order;
use App\Services\OrderAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function __construct(
        private readonly OrderAvailabilityService $availabilityService,
    ) {}

    public function blockedDays(Request $request, Car $car): JsonResponse
    {
        $token = $request->header('X-Integration-Token') ?: $request->query('token');
        if (! $token || $token !== $car->integration_token) {
            return response()->json(['message' => 'Invalid integration token.'], 401);
        }

        $from = $request->filled('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->filled('to') ? Carbon::parse($request->query('to')) : null;

        $bookingsQuery = Order::query()
            ->where('car_id', $car->id)
            ->where('order_status', OrderStatus::Confirmed)
            ->orderBy('pickup_at');

        if ($from) {
            $bookingsQuery->where('dropoff_at', '>=', $from);
        }
        if ($to) {
            $bookingsQuery->where('pickup_at', '<=', $to);
        }

        $bookings = $bookingsQuery
            ->get(['id', 'pickup_at', 'dropoff_at'])
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'start' => $order->pickup_at->toIso8601String(),
                'end' => $order->dropoff_at->toIso8601String(),
                'type' => 'booking',
            ])
            ->values();

        $blocks = $this->availabilityService->blockedWindowsForCar($car->id)
            ->filter(function (AvailabilityBlock $block) use ($from, $to) {
                if ($from && $block->ends_at < $from) {
                    return false;
                }
                if ($to && $block->starts_at > $to) {
                    return false;
                }

                return true;
            });

        $customBlocks = $blocks
            ->filter(fn (AvailabilityBlock $block) => $block->source === 'manual')
            ->map(fn (AvailabilityBlock $block) => [
                'id' => $block->id,
                'start' => $block->starts_at->toIso8601String(),
                'end' => $block->ends_at->toIso8601String(),
                'units_blocked' => $block->units_blocked,
                'notes' => $block->notes,
                'type' => 'custom_block',
            ])
            ->values();

        return response()->json([
            'vehicle' => [
                'id' => $car->id,
                'name' => $car->name,
                'units_available' => max(1, (int) $car->units_available),
            ],
            'bookings' => $bookings,
            'custom_blocks' => $customBlocks,
        ]);
    }
}
