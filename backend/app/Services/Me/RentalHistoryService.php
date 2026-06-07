<?php

namespace App\Services\Me;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\OrderStatus;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use App\Support\VehicleType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RentalHistoryService
{
    /**
     * @return array{items: Collection<int, array<string, mixed>>, summary: array<string, int>}
     */
    public function forUser(User $user, ?string $type = null, ?string $period = null): array
    {
        $items = collect();

        if ($this->includesVehicleType($type)) {
            $orders = $user->orders()
                ->with(['car.subCategory.mainCategory', 'pickupLocation', 'dropoffLocation'])
                ->orderByDesc('pickup_at')
                ->get();

            foreach ($orders as $order) {
                $vehicleType = VehicleType::fromSubCategory($order->car?->subCategory);

                if ($type === 'car' && $vehicleType !== 'car') {
                    continue;
                }

                if ($type === 'campervan' && $vehicleType !== 'campervan') {
                    continue;
                }

                $item = $this->orderToHistoryItem($order, $vehicleType);

                if ($this->matchesPeriod($item, $period)) {
                    $items->push($item);
                }
            }
        }

        if ($type === null || $type === 'guesthouse') {
            $bookings = $user->guestHouseBookings()
                ->with('guestHouse')
                ->orderByDesc('check_in')
                ->get();

            foreach ($bookings as $booking) {
                $item = $this->bookingToHistoryItem($booking);

                if ($this->matchesPeriod($item, $period)) {
                    $items->push($item);
                }
            }
        }

        $sorted = $items->sortByDesc('starts_at')->values();

        return [
            'items' => $sorted,
            'summary' => $this->buildSummary($sorted),
        ];
    }

    private function includesVehicleType(?string $type): bool
    {
        return $type === null || in_array($type, ['car', 'campervan', 'vehicle'], true);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function matchesPeriod(array $item, ?string $period): bool
    {
        if ($period === null || $period === 'all') {
            return true;
        }

        $endsAt = Carbon::parse($item['ends_at']);
        $isCancelled = $item['status'] === 'cancelled' || $item['cancelled_at'] !== null;

        if ($period === 'upcoming') {
            return $endsAt->isFuture() && ! $isCancelled;
        }

        if ($period === 'past') {
            return $endsAt->isPast() || $isCancelled || $item['status'] === 'completed';
        }

        return true;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array<string, int>
     */
    private function buildSummary(Collection $items): array
    {
        $now = now();

        return [
            'total' => $items->count(),
            'car' => $items->where('type', 'car')->count(),
            'campervan' => $items->where('type', 'campervan')->count(),
            'guesthouse' => $items->where('type', 'guesthouse')->count(),
            'upcoming' => $items->filter(function (array $item) use ($now): bool {
                $cancelled = $item['status'] === 'cancelled' || $item['cancelled_at'] !== null;

                return Carbon::parse($item['ends_at'])->isFuture() && ! $cancelled;
            })->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function orderToHistoryItem(Order $order, string $vehicleType): array
    {
        $confirmed = $order->order_status === OrderStatus::Confirmed;

        return [
            'kind' => 'order',
            'id' => $order->id,
            'type' => $vehicleType,
            'reference' => $order->reference,
            'title' => $order->car?->name ?? 'Vehicle rental',
            'subtitle' => $order->pickupLocation?->name,
            'status' => $order->order_status->value,
            'rental_status' => $order->rental_status?->value,
            'starts_at' => $order->pickup_at->toIso8601String(),
            'ends_at' => $order->dropoff_at->toIso8601String(),
            'total' => Money::formatDecimalFromCents((int) $order->total_cents),
            'currency' => $order->currency,
            'total_formatted' => Money::formatDecimalFromCents((int) $order->total_cents).' '.$order->currency,
            'thumbnail' => $order->car?->main_image_path,
            'listing_slug' => $order->car?->slug,
            'listing_id' => $order->car?->id,
            'created_at' => $order->created_at?->toIso8601String(),
            'cancelled_at' => $order->order_status === OrderStatus::Cancelled ? $order->updated_at?->toIso8601String() : null,
            'downloads' => [
                'calendar' => true,
                'contract' => $confirmed,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingToHistoryItem(GuestHouseBooking $booking): array
    {
        $house = $booking->guestHouse;
        $confirmed = in_array($booking->status, [
            GuestHouseBookingStatus::Confirmed,
            GuestHouseBookingStatus::Completed,
        ], true);

        return [
            'kind' => 'guesthouse',
            'id' => $booking->id,
            'type' => 'guesthouse',
            'reference' => $booking->booking_reference,
            'title' => $house?->name ?? 'Guesthouse stay',
            'subtitle' => $house?->city,
            'status' => $booking->status->value,
            'rental_status' => null,
            'starts_at' => $booking->check_in->startOfDay()->toIso8601String(),
            'ends_at' => $booking->check_out->startOfDay()->toIso8601String(),
            'nights' => $booking->nights,
            'total' => Money::formatDecimalFromCents((int) $booking->total_amount),
            'currency' => 'EUR',
            'total_formatted' => Money::formatDecimalFromCents((int) $booking->total_amount).' EUR',
            'thumbnail' => $house?->thumbnail,
            'listing_slug' => $house?->slug,
            'listing_id' => $house?->id,
            'created_at' => $booking->created_at?->toIso8601String(),
            'cancelled_at' => $booking->cancelled_at?->toIso8601String(),
            'downloads' => [
                'calendar' => false,
                'contract' => $confirmed,
            ],
        ];
    }
}
