<?php

namespace App\Services;

use Carbon\Carbon;

class RentalDurationService
{
    public function calculate(Carbon $pickupAt, Carbon $dropoffAt): array
    {
        if ($dropoffAt->lessThanOrEqualTo($pickupAt)) {
            throw new \InvalidArgumentException('Dropoff must be after pickup.');
        }

        $totalHours = $pickupAt->diffInHours($dropoffAt);
        $billableDays = (int) ceil($totalHours / 24);

        return [
            'hours' => $totalHours,
            'days' => max(1, $billableDays),
        ];
    }
}
