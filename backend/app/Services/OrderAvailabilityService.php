<?php

namespace App\Services;

use App\Models\AvailabilityBlock;
use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class OrderAvailabilityService
{
    public function confirmedUnitsBookedForCarInRange(int $carId, CarbonInterface $pickup, CarbonInterface $dropoff): int
    {
        return (int) Order::query()
            ->where('car_id', $carId)
            ->where('order_status', OrderStatus::Confirmed)
            ->where(function ($q) use ($pickup, $dropoff): void {
                $q->where('pickup_at', '<', $dropoff)
                    ->where('dropoff_at', '>', $pickup);
            })
            ->count();
    }

    public function lockedStandbyUnitsForCarInRange(int $carId, CarbonInterface $pickup, CarbonInterface $dropoff): int
    {
        return (int) Order::query()
            ->where('car_id', $carId)
            ->where('order_status', OrderStatus::StandBy)
            ->whereNotNull('payment_lock_expires_at')
            ->where('payment_lock_expires_at', '>', now())
            ->where(function ($q) use ($pickup, $dropoff): void {
                $q->where('pickup_at', '<', $dropoff)
                    ->where('dropoff_at', '>', $pickup);
            })
            ->count();
    }

    public function blockedUnitsForCarInRange(int $carId, CarbonInterface $pickup, CarbonInterface $dropoff): int
    {
        return (int) AvailabilityBlock::query()
            ->where('car_id', $carId)
            ->where('is_active', true)
            ->where(function ($q) use ($pickup, $dropoff): void {
                $q->where('starts_at', '<', $dropoff)
                    ->where('ends_at', '>', $pickup);
            })
            ->sum('units_blocked');
    }

    public function totalUnitsBlockedForCarInRange(int $carId, CarbonInterface $pickup, CarbonInterface $dropoff): int
    {
        return $this->confirmedUnitsBookedForCarInRange($carId, $pickup, $dropoff)
            + $this->lockedStandbyUnitsForCarInRange($carId, $pickup, $dropoff)
            + $this->blockedUnitsForCarInRange($carId, $pickup, $dropoff);
    }

    public function blockedWindowsForCar(int $carId): Collection
    {
        return AvailabilityBlock::query()
            ->where('car_id', $carId)
            ->where('is_active', true)
            ->orderBy('starts_at')
            ->get(['id', 'source', 'starts_at', 'ends_at', 'units_blocked', 'notes']);
    }

    public function standbyLockWindowsForCar(int $carId): Collection
    {
        return Order::query()
            ->where('car_id', $carId)
            ->where('order_status', OrderStatus::StandBy)
            ->whereNotNull('payment_lock_expires_at')
            ->where('payment_lock_expires_at', '>', now())
            ->orderBy('pickup_at')
            ->get(['id', 'pickup_at', 'dropoff_at', 'payment_lock_expires_at']);
    }

    public function hasCapacity(int $carId, int $unitsAvailable, CarbonInterface $pickup, CarbonInterface $dropoff): bool
    {
        $booked = $this->totalUnitsBlockedForCarInRange($carId, $pickup, $dropoff);

        return $booked < $unitsAvailable;
    }
}
