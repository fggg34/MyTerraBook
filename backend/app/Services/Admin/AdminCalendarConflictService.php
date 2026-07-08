<?php

namespace App\Services\Admin;

use App\Services\GuestHouseAvailabilityService;
use App\Services\OrderAvailabilityService;
use Illuminate\Support\Collection;

class AdminCalendarConflictService
{
    public function __construct(
        private readonly OrderAvailabilityService $orderAvailability,
        private readonly GuestHouseAvailabilityService $guestHouseAvailability,
    ) {}

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return list<array<string, mixed>>
     */
    public function detectConflicts(Collection $events): array
    {
        $conflicts = [];
        $flaggedIds = [];

        $byResource = $events->groupBy('resourceId');

        foreach ($byResource as $resourceId => $resourceEvents) {
            if (str_starts_with((string) $resourceId, 'car:')) {
                $carId = (int) str_replace('car:', '', (string) $resourceId);
                $capacity = (int) ($resourceEvents->first()['extendedProps']['capacity'] ?? 1);
                $carConflicts = $this->detectCarConflicts($carId, $capacity, $resourceEvents, $flaggedIds);
                $conflicts = array_merge($conflicts, $carConflicts);
            }

            if (str_starts_with((string) $resourceId, 'guesthouse:')) {
                $guestHouseId = (int) str_replace('guesthouse:', '', (string) $resourceId);
                $stayConflicts = $this->detectStayConflicts($guestHouseId, $resourceEvents, $flaggedIds);
                $conflicts = array_merge($conflicts, $stayConflicts);
            }
        }

        return [
            'conflicts' => $conflicts,
            'flaggedIds' => $flaggedIds,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return list<array<string, mixed>>
     */
    private function detectCarConflicts(int $carId, int $capacity, Collection $events, array &$flaggedIds): array
    {
        $conflicts = [];
        $active = $events->filter(fn (array $e) => in_array($e['status'], ['pending', 'confirmed', 'stand_by'], true));

        if ($active->count() < 2 && $capacity > 1) {
            return [];
        }

        $windows = $active->map(fn (array $e) => [
            'id' => $e['id'],
            'start' => $e['start'],
            'end' => $e['end'],
        ])->values();

        foreach ($windows as $i => $window) {
            $start = \Carbon\Carbon::parse($window['start']);
            $end = \Carbon\Carbon::parse($window['end']);
            $overlapping = 0;

            foreach ($windows as $j => $other) {
                if ($i === $j) {
                    continue;
                }
                $oStart = \Carbon\Carbon::parse($other['start']);
                $oEnd = \Carbon\Carbon::parse($other['end']);
                if ($oStart < $end && $oEnd > $start) {
                    $overlapping++;
                }
            }

            $bookedUnits = $this->orderAvailability->totalUnitsBlockedForCarInRange($carId, $start, $end);
            if ($bookedUnits > $capacity) {
                $flaggedIds[] = $window['id'];
                $conflicts[] = [
                    'resourceId' => "car:{$carId}",
                    'eventId' => $window['id'],
                    'bookedUnits' => $bookedUnits,
                    'capacity' => $capacity,
                    'date' => $start->toDateString(),
                ];
            }
        }

        return $conflicts;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $events
     * @return list<array<string, mixed>>
     */
    private function detectStayConflicts(int $guestHouseId, Collection $events, array &$flaggedIds): array
    {
        $conflicts = [];
        $active = $events->filter(fn (array $e) => in_array($e['status'], ['pending', 'confirmed'], true));

        if ($active->count() < 2) {
            return [];
        }

        $sorted = $active->sortBy('start')->values();

        for ($i = 0; $i < $sorted->count(); $i++) {
            for ($j = $i + 1; $j < $sorted->count(); $j++) {
                $a = $sorted[$i];
                $b = $sorted[$j];
                $aStart = \Carbon\Carbon::parse($a['start'])->toDateString();
                $aEnd = \Carbon\Carbon::parse($a['end'])->toDateString();
                $bStart = \Carbon\Carbon::parse($b['start'])->toDateString();
                $bEnd = \Carbon\Carbon::parse($b['end'])->toDateString();

                if ($aStart < $bEnd && $aEnd > $bStart) {
                    $flaggedIds[] = $a['id'];
                    $flaggedIds[] = $b['id'];
                    $conflicts[] = [
                        'resourceId' => "guesthouse:{$guestHouseId}",
                        'eventIds' => [$a['id'], $b['id']],
                        'date' => $bStart,
                    ];
                }
            }
        }

        return $conflicts;
    }

    public function carOrderHasConflict(Order $order): bool
    {
        if (! $order->car_id || ! $order->pickup_at || ! $order->dropoff_at) {
            return false;
        }

        $capacity = (int) ($order->car?->units_available ?? 1);
        $booked = $this->orderAvailability->totalUnitsBlockedForCarInRange(
            $order->car_id,
            $order->pickup_at,
            $order->dropoff_at,
            $order->id,
        );

        return $booked >= $capacity;
    }

    public function stayBookingHasConflict(GuestHouseBooking $booking): bool
    {
        if (! $booking->guest_house_id || ! $booking->check_in || ! $booking->check_out) {
            return false;
        }

        return $this->guestHouseAvailability->hasBookingConflict(
            $booking->guest_house_id,
            $booking->check_in->toDateString(),
            $booking->check_out->toDateString(),
            $booking->id,
        );
    }
}
