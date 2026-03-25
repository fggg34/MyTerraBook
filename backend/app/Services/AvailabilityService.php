<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\CarUnavailability;
use Carbon\Carbon;

class AvailabilityService
{
    public function hasConflict(int $carId, Carbon $pickupAt, Carbon $dropoffAt, ?int $ignoreBookingId = null): bool
    {
        $bookingConflicts = Booking::query()
            ->where('car_id', $carId)
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->when($ignoreBookingId, fn ($query) => $query->where('id', '!=', $ignoreBookingId))
            ->where(function ($query) use ($pickupAt, $dropoffAt) {
                $query
                    ->whereBetween('pickup_at', [$pickupAt, $dropoffAt])
                    ->orWhereBetween('dropoff_at', [$pickupAt, $dropoffAt])
                    ->orWhere(function ($nested) use ($pickupAt, $dropoffAt) {
                        $nested
                            ->where('pickup_at', '<=', $pickupAt)
                            ->where('dropoff_at', '>=', $dropoffAt);
                    });
            })
            ->exists();

        if ($bookingConflicts) {
            return true;
        }

        return CarUnavailability::query()
            ->where('car_id', $carId)
            ->where(function ($query) use ($pickupAt, $dropoffAt) {
                $query
                    ->whereBetween('starts_at', [$pickupAt, $dropoffAt])
                    ->orWhereBetween('ends_at', [$pickupAt, $dropoffAt])
                    ->orWhere(function ($nested) use ($pickupAt, $dropoffAt) {
                        $nested
                            ->where('starts_at', '<=', $pickupAt)
                            ->where('ends_at', '>=', $dropoffAt);
                    });
            })
            ->exists();
    }
}
