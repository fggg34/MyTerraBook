<?php

namespace App\Services\Admin;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use Carbon\CarbonInterface;

class AdminCalendarSummaryService
{
    /**
     * @return array<string, mixed>
     */
    public function summarize(
        CarbonInterface $start,
        CarbonInterface $end,
        AdminCalendarFilters $filters,
    ): array {
        $carStats = $filters->includesVehicles()
            ? $this->carStats($start, $end, $filters)
            : ['count' => 0, 'revenueCents' => 0, 'listingDays' => 0, 'capacityDays' => 0, 'pending' => 0];

        $stayStats = $filters->includesGuesthouses()
            ? $this->stayStats($start, $end, $filters)
            : ['count' => 0, 'revenueCents' => 0, 'listingDays' => 0, 'capacityDays' => 0, 'pending' => 0];

        $totalReservations = $carStats['count'] + $stayStats['count'];
        $revenueCents = $carStats['revenueCents'] + $stayStats['revenueCents'];
        $bookedDays = $carStats['listingDays'] + $stayStats['listingDays'];
        $capacityDays = $carStats['capacityDays'] + $stayStats['capacityDays'];
        $pendingApprovals = $carStats['pending'] + $stayStats['pending'];

        $rangeDays = max(1, $start->diffInDays($end));
        $occupancyRate = $capacityDays > 0
            ? round(($bookedDays / $capacityDays) * 100, 1)
            : 0.0;

        return [
            'totalReservations' => $totalReservations,
            'revenueCents' => $revenueCents,
            'occupancyRate' => $occupancyRate,
            'pendingApprovals' => $pendingApprovals,
            'rangeDays' => $rangeDays,
            'vehicleReservations' => $carStats['count'],
            'guesthouseReservations' => $stayStats['count'],
        ];
    }

    /**
     * @return array{count: int, revenueCents: int, listingDays: int, capacityDays: int, pending: int}
     */
    private function carStats(CarbonInterface $start, CarbonInterface $end, AdminCalendarFilters $filters): array
    {
        $query = Order::query()
            ->with('car:id,units_available')
            ->where('pickup_at', '<', $end)
            ->where('dropoff_at', '>', $start);

        if ($filters->hostId) {
            $query->whereHas('car', fn ($q) => $q->where('user_id', $filters->hostId));
        }

        $orders = $query->get();
        $count = $orders->count();
        $revenueCents = (int) $orders
            ->where('order_status', OrderStatus::Confirmed)
            ->sum('total_cents');
        $pending = $orders->whereIn('order_status', [OrderStatus::Pending, OrderStatus::StandBy])->count();

        $listingDays = 0;
        foreach ($orders->whereIn('order_status', [OrderStatus::Confirmed, OrderStatus::StandBy, OrderStatus::Pending]) as $order) {
            $overlapStart = $order->pickup_at->greaterThan($start) ? $order->pickup_at : $start->copy();
            $overlapEnd = $order->dropoff_at->lessThan($end) ? $order->dropoff_at : $end->copy();
            if ($overlapEnd > $overlapStart) {
                $listingDays += max(1, (int) ceil($overlapStart->diffInHours($overlapEnd) / 24));
            }
        }

        $carQuery = Car::query()->select(['id', 'units_available']);
        if ($filters->hostId) {
            $carQuery->where('user_id', $filters->hostId);
        }
        $totalUnits = (int) $carQuery->sum('units_available');
        $capacityDays = max(1, $totalUnits) * max(1, (int) $start->diffInDays($end));

        return [
            'count' => $count,
            'revenueCents' => $revenueCents,
            'listingDays' => $listingDays,
            'capacityDays' => $capacityDays,
            'pending' => $pending,
        ];
    }

    /**
     * @return array{count: int, revenueCents: int, listingDays: int, capacityDays: int, pending: int}
     */
    private function stayStats(CarbonInterface $start, CarbonInterface $end, AdminCalendarFilters $filters): array
    {
        $query = GuestHouseBooking::query()
            ->where('check_in', '<', $end->toDateString())
            ->where('check_out', '>', $start->toDateString());

        if ($filters->hostId) {
            $query->whereHas('guestHouse', fn ($q) => $q->where('user_id', $filters->hostId));
        }

        $bookings = $query->get();
        $count = $bookings->count();
        $revenueCents = (int) $bookings
            ->where('status', GuestHouseBookingStatus::Confirmed)
            ->sum('total_amount');
        $pending = $bookings->where('status', GuestHouseBookingStatus::Pending)->count();

        $listingDays = 0;
        foreach ($bookings->whereIn('status', [
            GuestHouseBookingStatus::Confirmed,
            GuestHouseBookingStatus::Pending,
        ]) as $booking) {
            $listingDays += max(1, (int) $booking->nights);
        }

        $houseQuery = GuestHouse::query();
        if ($filters->hostId) {
            $houseQuery->where('user_id', $filters->hostId);
        }
        $houseCount = $houseQuery->count();
        $capacityDays = max(1, $houseCount) * max(1, (int) $start->diffInDays($end));

        return [
            'count' => $count,
            'revenueCents' => $revenueCents,
            'listingDays' => $listingDays,
            'capacityDays' => $capacityDays,
            'pending' => $pending,
        ];
    }
}
