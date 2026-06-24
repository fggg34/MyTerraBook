<?php

namespace App\Services;

use App\Enums\BookingChangeRequestStatus;
use App\Enums\BookingChangeRequestType;
use App\Enums\GuestHouseBookingStatus;
use App\Enums\OrderStatus;
use App\Models\BookingChangeRequest;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Models\User;
use App\Services\Order\OrderPricingSyncService;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingChangeRequestService
{
    public function __construct(
        private readonly RentalQuoteService $quoteService,
        private readonly OrderAvailabilityService $availabilityService,
        private readonly OrderPricingSyncService $pricingSync,
    ) {}

    public function resolveBookable(string $kind, string $reference, ?string $email = null): Model
    {
        if ($kind === 'order') {
            $order = Order::query()->where('reference', $reference)->first();
            if (! $order) {
                throw new InvalidArgumentException('Booking not found.');
            }
            if ($email && strcasecmp($order->customer_email, $email) !== 0) {
                throw new InvalidArgumentException('Email does not match this booking.');
            }

            return $order;
        }

        if ($kind === 'guesthouse') {
            $booking = GuestHouseBooking::query()->where('booking_reference', $reference)->first();
            if (! $booking) {
                throw new InvalidArgumentException('Booking not found.');
            }
            if ($email && strcasecmp($booking->guest_email, $email) !== 0) {
                throw new InvalidArgumentException('Email does not match this booking.');
            }

            return $booking;
        }

        throw new InvalidArgumentException('Invalid booking type.');
    }

    public function userCanAccessBookable(User $user, Model $bookable): bool
    {
        if ($bookable instanceof Order) {
            return (int) $bookable->user_id === (int) $user->id
                || strcasecmp($bookable->customer_email, $user->email) === 0;
        }

        if ($bookable instanceof GuestHouseBooking) {
            return (int) $bookable->user_id === (int) $user->id
                || strcasecmp($bookable->guest_email, $user->email) === 0;
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(Model $bookable, ?User $user, array $payload): BookingChangeRequest
    {
        $this->assertBookableIsModifiable($bookable);

        $type = BookingChangeRequestType::from($payload['type']);
        $requestedChanges = $payload['requested_changes'] ?? [];

        if ($type === BookingChangeRequestType::Modification && $bookable instanceof Order) {
            $preview = $this->previewOrderModification($bookable, $requestedChanges);
            $pricingAfter = $preview['quote'];
            $priceDelta = (int) $pricingAfter['total_cents'] - (int) $bookable->total_cents;
        } else {
            $pricingAfter = null;
            $priceDelta = null;
        }

        return BookingChangeRequest::query()->create([
            'bookable_type' => $bookable::class,
            'bookable_id' => $bookable->getKey(),
            'user_id' => $user?->id,
            'type' => $type,
            'status' => BookingChangeRequestStatus::Pending,
            'customer_message' => trim((string) $payload['customer_message']),
            'requested_changes' => $requestedChanges ?: null,
            'pricing_before' => $this->pricingBefore($bookable),
            'pricing_after' => $pricingAfter,
            'price_delta_cents' => $priceDelta,
        ]);
    }

    /**
     * @param  array<string, mixed>  $requestedChanges
     * @return array{quote: array<string, mixed>, total_formatted: string, price_delta_cents: int}
     */
    public function previewOrderModification(Order $order, array $requestedChanges): array
    {
        $car = $order->car;
        if (! $car) {
            throw new InvalidArgumentException('Vehicle not found for this booking.');
        }

        $pickup = Carbon::parse($requestedChanges['pickup_at'] ?? $order->pickup_at);
        $dropoff = Carbon::parse($requestedChanges['dropoff_at'] ?? $order->dropoff_at);

        if ($dropoff <= $pickup) {
            throw new InvalidArgumentException('Drop-off must be after pick-up.');
        }

        if (! $this->availabilityService->hasCapacity($car->id, (int) $car->units_available, $pickup, $dropoff, $order->id)) {
            throw new InvalidArgumentException('No availability for the requested dates.');
        }

        $quote = $this->quoteService->quote(
            $car,
            (int) ($requestedChanges['price_type_id'] ?? $order->price_type_id),
            $pickup,
            $dropoff,
            (int) ($requestedChanges['pickup_location_id'] ?? $order->pickup_location_id),
            (int) ($requestedChanges['dropoff_location_id'] ?? $order->dropoff_location_id),
            $requestedChanges['rental_options'] ?? $order->rentalOptions()->pluck('rental_option_id')->all(),
            $requestedChanges['coupon_code'] ?? null,
        );

        return [
            'quote' => $quote,
            'total_formatted' => Money::formatDecimalFromCents($quote['total_cents']).' '.$quote['currency'],
            'price_delta_cents' => (int) $quote['total_cents'] - (int) $order->total_cents,
        ];
    }

    public function apply(BookingChangeRequest $request, User $admin, ?string $adminResponse = null): BookingChangeRequest
    {
        if ($request->status !== BookingChangeRequestStatus::Pending) {
            throw new InvalidArgumentException('Only pending requests can be applied.');
        }

        $bookable = $request->bookable;
        if (! $bookable) {
            throw new InvalidArgumentException('Booking not found.');
        }

        return DB::transaction(function () use ($request, $admin, $adminResponse, $bookable) {
            if ($request->type === BookingChangeRequestType::Cancellation) {
                $this->applyCancellation($bookable);
            } else {
                $this->applyModification($bookable, $request->requested_changes ?? []);
            }

            $request->update([
                'status' => BookingChangeRequestStatus::Applied,
                'admin_response' => $adminResponse,
                'reviewed_by_id' => $admin->id,
                'reviewed_at' => now(),
                'applied_at' => now(),
            ]);

            return $request->fresh(['reviewer']);
        });
    }

    public function reject(BookingChangeRequest $request, User $admin, string $adminResponse): BookingChangeRequest
    {
        if ($request->status !== BookingChangeRequestStatus::Pending) {
            throw new InvalidArgumentException('Only pending requests can be rejected.');
        }

        $request->update([
            'status' => BookingChangeRequestStatus::Rejected,
            'admin_response' => $adminResponse,
            'reviewed_by_id' => $admin->id,
            'reviewed_at' => now(),
        ]);

        return $request->fresh(['reviewer']);
    }

    private function applyCancellation(Model $bookable): void
    {
        if ($bookable instanceof Order) {
            $bookable->transitionOrderStatus(OrderStatus::Cancelled);

            return;
        }

        if ($bookable instanceof GuestHouseBooking) {
            $bookable->update([
                'status' => GuestHouseBookingStatus::Cancelled,
                'cancellation_reason' => 'Cancelled via modification request',
                'cancelled_at' => now(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $requestedChanges
     */
    private function applyModification(Model $bookable, array $requestedChanges): void
    {
        if ($bookable instanceof Order) {
            $preview = $this->previewOrderModification($bookable, $requestedChanges);
            $this->pricingSync->applyQuoteToOrder($bookable, $preview['quote'], $requestedChanges);

            return;
        }

        if ($bookable instanceof GuestHouseBooking) {
            if (isset($requestedChanges['check_in'])) {
                $bookable->check_in = Carbon::parse($requestedChanges['check_in'])->toDateString();
            }
            if (isset($requestedChanges['check_out'])) {
                $bookable->check_out = Carbon::parse($requestedChanges['check_out'])->toDateString();
            }
            if (isset($requestedChanges['guests_count'])) {
                $bookable->guests_count = (int) $requestedChanges['guests_count'];
            }
            $bookable->save();
        }
    }

    private function assertBookableIsModifiable(Model $bookable): void
    {
        if ($bookable instanceof Order) {
            if ($bookable->order_status !== OrderStatus::Confirmed) {
                throw new InvalidArgumentException('This booking cannot be modified.');
            }

            return;
        }

        if ($bookable instanceof GuestHouseBooking) {
            if (! in_array($bookable->status, [GuestHouseBookingStatus::Pending, GuestHouseBookingStatus::Confirmed], true)) {
                throw new InvalidArgumentException('This booking cannot be modified.');
            }
        }
    }

    private function pricingBefore(Model $bookable): array
    {
        if ($bookable instanceof Order) {
            return $this->pricingSync->pricingSnapshotForOrder($bookable);
        }

        if ($bookable instanceof GuestHouseBooking) {
            return [
                'total_cents' => (int) $bookable->total_amount,
                'currency' => 'EUR',
                'check_in' => $bookable->check_in?->toDateString(),
                'check_out' => $bookable->check_out?->toDateString(),
            ];
        }

        return [];
    }
}
