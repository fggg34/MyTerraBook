<?php

namespace App\Services\Admin;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class AdminCalendarEventService
{
    public function __construct(
        private readonly AdminCalendarConflictService $conflictService,
    ) {}

    /**
     * @return array{data: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function eventsForRange(
        CarbonInterface $start,
        CarbonInterface $end,
        AdminCalendarFilters $filters,
    ): array {
        $events = collect();

        if ($filters->includesVehicles()) {
            $events = $events->merge($this->carEvents($start, $end, $filters));
        }

        if ($filters->includesGuesthouses()) {
            $events = $events->merge($this->stayEvents($start, $end, $filters));
        }

        $sorted = $events->sortBy('start')->values();
        $conflictResult = $this->conflictService->detectConflicts($sorted);
        $flaggedIds = array_flip($conflictResult['flaggedIds']);

        $data = $sorted->map(function (array $event) use ($flaggedIds) {
            $event['hasConflict'] = isset($flaggedIds[$event['id']]);

            return $event;
        })->values()->all();

        return [
            'data' => $data,
            'meta' => [
                'total' => count($data),
                'conflicts' => $conflictResult['conflicts'],
                'range' => [
                    'start' => $start->toIso8601String(),
                    'end' => $end->toIso8601String(),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function showCarOrder(Order $order): ?array
    {
        $order->load([
            'car.host',
            'user',
            'pickupLocation',
            'dropoffLocation',
            'payments' => fn ($q) => $q->latest()->limit(3),
        ]);

        return $this->mapCarOrder($order, includeDetail: true);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function showStayBooking(GuestHouseBooking $booking): ?array
    {
        $booking->load([
            'guestHouse.host',
            'user',
            'payments' => fn ($q) => $q->latest()->limit(3),
        ]);

        return $this->mapStayBooking($booking, includeDetail: true);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function carEvents(CarbonInterface $start, CarbonInterface $end, AdminCalendarFilters $filters): \Illuminate\Support\Collection
    {
        $query = Order::query()
            ->with([
                'car:id,name,user_id,units_available',
                'car.host:id,name,email',
                'user:id,name,email,phone',
                'payments' => fn ($q) => $q->latest()->limit(1),
            ])
            ->where('pickup_at', '<', $end)
            ->where('dropoff_at', '>', $start);

        $this->applyCarFilters($query, $filters);

        return $query->orderBy('pickup_at')->get()
            ->map(fn (Order $order) => $this->mapCarOrder($order))
            ->filter();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function stayEvents(CarbonInterface $start, CarbonInterface $end, AdminCalendarFilters $filters): \Illuminate\Support\Collection
    {
        $query = GuestHouseBooking::query()
            ->with([
                'guestHouse:id,name,user_id,city',
                'guestHouse.host:id,name,email',
                'user:id,name,email,phone',
            ])
            ->where('check_in', '<', $end->toDateString())
            ->where('check_out', '>', $start->toDateString());

        $this->applyStayFilters($query, $filters);

        return $query->orderBy('check_in')->get()
            ->map(fn (GuestHouseBooking $booking) => $this->mapStayBooking($booking))
            ->filter();
    }

    /**
     * @param  Builder<Order>  $query
     */
    private function applyCarFilters(Builder $query, AdminCalendarFilters $filters): void
    {
        $carIds = $filters->carIds();
        if ($carIds !== []) {
            $query->whereIn('car_id', $carIds);
        }

        if ($filters->status) {
            $this->applyCarStatusFilter($query, $filters->status);
        }

        if ($filters->hostId) {
            $query->whereHas('car', fn ($q) => $q->where('user_id', $filters->hostId));
        }

        if ($filters->city) {
            $query->whereHas('car.locations', fn ($q) => $q
                ->where('name', 'like', '%'.$filters->city.'%')
                ->orWhere('address', 'like', '%'.$filters->city.'%'));
        }

        if ($filters->search) {
            $term = '%'.$filters->search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('reference', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('customer_email', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term))
                    ->orWhereHas('car.host', fn ($h) => $h->where('name', 'like', $term));
            });
        }
    }

    /**
     * @param  Builder<GuestHouseBooking>  $query
     */
    private function applyStayFilters(Builder $query, AdminCalendarFilters $filters): void
    {
        $guestHouseIds = $filters->guestHouseIds();
        if ($guestHouseIds !== []) {
            $query->whereIn('guest_house_id', $guestHouseIds);
        }

        if ($filters->status) {
            $query->where('status', $filters->status);
        }

        if ($filters->hostId) {
            $query->whereHas('guestHouse', fn ($q) => $q->where('user_id', $filters->hostId));
        }

        if ($filters->city) {
            $query->whereHas('guestHouse', fn ($q) => $q->where('city', 'like', '%'.$filters->city.'%'));
        }

        if ($filters->search) {
            $term = '%'.$filters->search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('booking_reference', 'like', $term)
                    ->orWhere('guest_name', 'like', $term)
                    ->orWhere('guest_email', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term))
                    ->orWhereHas('guestHouse.host', fn ($h) => $h->where('name', 'like', $term));
            });
        }
    }

    /**
     * @param  Builder<Order>  $query
     */
    private function applyCarStatusFilter(Builder $query, string $status): void
    {
        match ($status) {
            'completed' => $query->where('order_status', OrderStatus::Confirmed)
                ->where('rental_status', RentalStatus::Terminated),
            'no_show' => $query->where('order_status', OrderStatus::Confirmed)
                ->where('rental_status', RentalStatus::NoShow),
            'stand_by' => $query->where('order_status', OrderStatus::StandBy),
            'confirmed' => $query->where('order_status', OrderStatus::Confirmed)
                ->where(function ($q): void {
                    $q->whereNull('rental_status')
                        ->orWhereIn('rental_status', [RentalStatus::Upcoming, RentalStatus::Started]);
                }),
            default => $query->where('order_status', $status),
        };
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapCarOrder(Order $order, bool $includeDetail = false): ?array
    {
        if (! $order->pickup_at || ! $order->dropoff_at || ! $order->car_id) {
            return null;
        }

        $guestName = $order->customer_name ?: $order->user?->name ?: 'Guest';
        $status = $this->normalizeCarStatus($order);
        $paymentStatus = $this->resolveCarPaymentStatus($order);
        $capacity = max(1, (int) ($order->car?->units_available ?? 1));

        $event = [
            'id' => 'car:'.$order->id,
            'type' => 'vehicle',
            'resourceId' => 'car:'.$order->car_id,
            'title' => $guestName.' · '.$order->reference,
            'start' => $order->pickup_at->toIso8601String(),
            'end' => $order->dropoff_at->toIso8601String(),
            'status' => $status,
            'subStatus' => $order->rental_status?->value,
            'paymentStatus' => $paymentStatus,
            'hasConflict' => false,
            'extendedProps' => [
                'reference' => $order->reference,
                'guestName' => $guestName,
                'guestEmail' => $order->customer_email ?: $order->user?->email,
                'guestPhone' => $order->customer_phone ?: $order->user?->phone,
                'hostName' => $order->car?->host?->name,
                'hostEmail' => $order->car?->host?->email,
                'listingName' => $order->car?->name,
                'totalCents' => $order->total_cents,
                'currency' => $order->currency,
                'capacity' => $capacity,
                'notes' => $order->notes,
            ],
        ];

        if ($includeDetail) {
            $event['extendedProps']['adminInternalNote'] = $order->admin_internal_note;
            $event['extendedProps']['pickupLocation'] = $order->pickupLocation?->name;
            $event['extendedProps']['dropoffLocation'] = $order->dropoffLocation?->name;
            $event['extendedProps']['payments'] = $order->payments->map(fn ($p) => [
                'status' => $p->status,
                'amountCents' => $p->amount_cents,
                'method' => $p->method_code,
                'processedAt' => $p->processed_at?->toIso8601String(),
            ])->all();
            $event['hasConflict'] = $this->conflictService->carOrderHasConflict($order);
        }

        return $event;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function mapStayBooking(GuestHouseBooking $booking, bool $includeDetail = false): ?array
    {
        if (! $booking->check_in || ! $booking->check_out || ! $booking->guest_house_id) {
            return null;
        }

        $guestName = $booking->guest_name ?: $booking->user?->name ?: 'Guest';
        $checkIn = Carbon::parse($booking->check_in)->startOfDay();
        $checkOut = Carbon::parse($booking->check_out)->startOfDay();

        $event = [
            'id' => 'stay:'.$booking->id,
            'type' => 'guesthouse',
            'resourceId' => 'guesthouse:'.$booking->guest_house_id,
            'title' => $guestName.' · '.$booking->booking_reference,
            'start' => $checkIn->toIso8601String(),
            'end' => $checkOut->toIso8601String(),
            'status' => $booking->status->value,
            'subStatus' => null,
            'paymentStatus' => $booking->payment_status ?? 'pending',
            'hasConflict' => false,
            'extendedProps' => [
                'reference' => $booking->booking_reference,
                'guestName' => $guestName,
                'guestEmail' => $booking->guest_email ?: $booking->user?->email,
                'guestPhone' => $booking->guest_phone ?: $booking->user?->phone,
                'hostName' => $booking->guestHouse?->host?->name,
                'hostEmail' => $booking->guestHouse?->host?->email,
                'listingName' => $booking->guestHouse?->name,
                'city' => $booking->guestHouse?->city,
                'totalCents' => $booking->total_amount,
                'currency' => 'EUR',
                'capacity' => 1,
                'nights' => $booking->nights,
                'guestsCount' => $booking->guests_count,
                'notes' => $booking->special_requests,
            ],
        ];

        if ($includeDetail) {
            $event['extendedProps']['cancellationReason'] = $booking->cancellation_reason;
            $event['extendedProps']['payments'] = $booking->payments->map(fn ($p) => [
                'status' => $p->status ?? 'unknown',
                'amountCents' => $p->amount ?? null,
            ])->all();
            $event['hasConflict'] = $this->conflictService->stayBookingHasConflict($booking);
        }

        return $event;
    }

    private function normalizeCarStatus(Order $order): string
    {
        if ($order->order_status === OrderStatus::Confirmed) {
            if ($order->rental_status === RentalStatus::Terminated) {
                return 'completed';
            }
            if ($order->rental_status === RentalStatus::NoShow) {
                return 'no_show';
            }
        }

        return $order->order_status->value;
    }

    private function resolveCarPaymentStatus(Order $order): string
    {
        $latest = $order->payments->first();
        if ($latest?->status) {
            return $latest->status;
        }

        return match ($order->order_status) {
            OrderStatus::Confirmed => 'confirmed',
            OrderStatus::Pending, OrderStatus::StandBy => 'pending',
            OrderStatus::Cancelled => 'cancelled',
        };
    }
}
