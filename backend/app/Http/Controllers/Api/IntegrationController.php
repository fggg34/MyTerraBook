<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    public function __construct(
        private readonly OrderAvailabilityService $availabilityService,
    ) {}

    public function blockedDays(Request $request): JsonResponse
    {
        $token = $request->header('X-Integration-Token') ?: $request->query('token');
        if (! $token) {
            return response()->json(['message' => 'Invalid integration token.'], 401);
        }

        $host = User::query()
            ->where('integration_token', $token)
            ->whereIn('role', [UserRole::Host, UserRole::Admin])
            ->first();

        if (! $host) {
            return response()->json(['message' => 'Invalid integration token.'], 401);
        }

        $from = $request->filled('from') ? Carbon::parse($request->query('from')) : null;
        $to = $request->filled('to') ? Carbon::parse($request->query('to')) : null;

        $cars = Car::query()
            ->where('user_id', $host->id)
            ->orderBy('name')
            ->get();

        $vehicles = $cars->map(fn (Car $car) => [
            'id' => $car->id,
            'name' => $car->name,
            'units_available' => max(1, (int) $car->units_available),
            'bookings' => $this->bookingsForCar($car->id, $from, $to),
            'custom_blocks' => $this->customBlocksForCar($car->id, $from, $to),
        ])->values();

        return response()->json(['vehicles' => $vehicles]);
    }

    private function bookingsForCar(int $carId, ?Carbon $from, ?Carbon $to): array
    {
        $query = Order::query()
            ->where('car_id', $carId)
            ->where('order_status', OrderStatus::Confirmed)
            ->orderBy('pickup_at');

        if ($from) {
            $query->where('dropoff_at', '>=', $from);
        }
        if ($to) {
            $query->where('pickup_at', '<=', $to);
        }

        return $query
            ->get(['id', 'pickup_at', 'dropoff_at'])
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'start' => $order->pickup_at->toIso8601String(),
                'end' => $order->dropoff_at->toIso8601String(),
                'type' => 'booking',
            ])
            ->values()
            ->all();
    }

    private function customBlocksForCar(int $carId, ?Carbon $from, ?Carbon $to): array
    {
        return $this->availabilityService->blockedWindowsForCar($carId)
            ->filter(function (AvailabilityBlock $block) use ($from, $to) {
                if ($from && $block->ends_at < $from) {
                    return false;
                }
                if ($to && $block->starts_at > $to) {
                    return false;
                }

                return $block->source === 'manual';
            })
            ->map(fn (AvailabilityBlock $block) => [
                'id' => $block->id,
                'start' => $block->starts_at->toIso8601String(),
                'end' => $block->ends_at->toIso8601String(),
                'units_blocked' => $block->units_blocked,
                'notes' => $block->notes,
                'type' => 'custom_block',
            ])
            ->values()
            ->all();
    }
}
