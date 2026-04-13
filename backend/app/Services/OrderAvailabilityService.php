<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Carbon\CarbonInterface;

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

    public function hasCapacity(int $carId, int $unitsAvailable, CarbonInterface $pickup, CarbonInterface $dropoff): bool
    {
        $booked = $this->confirmedUnitsBookedForCarInRange($carId, $pickup, $dropoff);

        return $booked < $unitsAvailable;
    }
}
