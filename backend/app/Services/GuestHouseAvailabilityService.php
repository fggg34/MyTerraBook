<?php

namespace App\Services;

use App\Enums\GuestHouseBookingStatus;
use App\Models\GuestHouse;
use App\Models\GuestHouseAvailabilityBlock;
use App\Models\GuestHouseBooking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class GuestHouseAvailabilityService
{
    public function isAvailable(GuestHouse $house, string $checkIn, string $checkOut): bool
    {
        if ($this->hasBookingConflict($house->id, $checkIn, $checkOut)) {
            return false;
        }

        return ! $this->hasBlockConflict($house->id, $checkIn, $checkOut);
    }

    /**
     * @return list<string> Y-m-d dates that are unavailable
     */
    public function getBlockedDates(GuestHouse $house, string $from, string $to): array
    {
        $dates = [];
        $period = CarbonPeriod::create($from, $to);

        foreach ($period as $day) {
            $date = $day->toDateString();
            $next = $day->copy()->addDay()->toDateString();

            if (! $this->isAvailable($house, $date, $next)) {
                $dates[] = $date;
            }
        }

        return array_values(array_unique($dates));
    }

    /**
     * @param  array{blocked_from: string, blocked_to: string, reason: string, note?: string|null, source?: string, ical_uid?: string|null}  $data
     */
    public function blockDates(GuestHouse $house, array $data): GuestHouseAvailabilityBlock
    {
        return $house->availabilityBlocks()->create($data);
    }

    public function hasBookingConflict(int $guestHouseId, string $checkIn, string $checkOut, ?int $exceptBookingId = null): bool
    {
        $query = GuestHouseBooking::query()
            ->where('guest_house_id', $guestHouseId)
            ->whereIn('status', [
                GuestHouseBookingStatus::Pending,
                GuestHouseBookingStatus::Confirmed,
            ])
            ->where('check_in', '<', $checkOut)
            ->where('check_out', '>', $checkIn);

        if ($exceptBookingId !== null) {
            $query->whereKeyNot($exceptBookingId);
        }

        return $query->exists();
    }

    public function hasBlockConflict(int $guestHouseId, string $checkIn, string $checkOut): bool
    {
        return GuestHouseAvailabilityBlock::query()
            ->where('guest_house_id', $guestHouseId)
            ->where('blocked_from', '<=', Carbon::parse($checkOut)->subDay()->toDateString())
            ->where('blocked_to', '>=', $checkIn)
            ->exists();
    }
}
