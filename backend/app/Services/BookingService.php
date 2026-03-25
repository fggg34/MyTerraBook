<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Mail\BookingCreatedMail;
use App\Models\Booking;
use App\Models\Car;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Extra;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BookingService
{
    public function quote(array $payload): array
    {
        $car = Car::query()->findOrFail($payload['car_id']);
        $pickupAt = Carbon::parse($payload['pickup_at']);
        $dropoffAt = Carbon::parse($payload['dropoff_at']);

        $coupon = null;
        if (! empty($payload['coupon_code'])) {
            $coupon = Coupon::query()->where('code', strtoupper($payload['coupon_code']))->first();
        }

        return app(PricingService::class)->quote(
            car: $car,
            pickupAt: $pickupAt,
            dropoffAt: $dropoffAt,
            pickupLocationId: (int) $payload['pickup_location_id'],
            extras: $payload['extras'] ?? [],
            coupon: $coupon,
        );
    }

    public function create(array $payload, ?int $authenticatedUserId = null, ?int $adminCreatorId = null): Booking
    {
        return DB::transaction(function () use ($payload, $authenticatedUserId, $adminCreatorId): Booking {
            $car = Car::query()->findOrFail($payload['car_id']);
            $pickupAt = Carbon::parse($payload['pickup_at']);
            $dropoffAt = Carbon::parse($payload['dropoff_at']);

            if (app(AvailabilityService::class)->hasConflict($car->id, $pickupAt, $dropoffAt)) {
                abort(422, 'Selected car is not available for the selected dates.');
            }

            $coupon = null;
            if (! empty($payload['coupon_code'])) {
                $coupon = Coupon::query()->where('code', strtoupper($payload['coupon_code']))->first();
            }

            $quote = app(PricingService::class)->quote(
                car: $car,
                pickupAt: $pickupAt,
                dropoffAt: $dropoffAt,
                pickupLocationId: (int) $payload['pickup_location_id'],
                extras: $payload['extras'] ?? [],
                coupon: $coupon,
            );

            $booking = Booking::query()->create([
                'reference' => $this->generateReference(),
                'user_id' => $authenticatedUserId,
                'car_id' => $car->id,
                'pickup_location_id' => $payload['pickup_location_id'],
                'dropoff_location_id' => $payload['dropoff_location_id'],
                'pickup_at' => $pickupAt,
                'dropoff_at' => $dropoffAt,
                'status' => BookingStatus::Pending,
                'customer_name' => $payload['customer_name'],
                'customer_email' => $payload['customer_email'],
                'customer_phone' => $payload['customer_phone'] ?? null,
                'rental_subtotal' => $quote['rental_subtotal'],
                'extras_subtotal' => $quote['extras_subtotal'],
                'discount_amount' => $quote['discount_amount'],
                'tax_amount' => $quote['tax_amount'],
                'total' => $quote['total'],
                'coupon_id' => $coupon?->id,
                'currency' => $payload['currency'] ?? 'USD',
                'pricing_snapshot' => $quote,
                'notes' => $payload['notes'] ?? null,
                'created_by_admin_id' => $adminCreatorId,
            ]);

            $this->attachExtras($booking, $payload['extras'] ?? [], $quote);

            if ($coupon) {
                CouponRedemption::query()->create([
                    'coupon_id' => $coupon->id,
                    'booking_id' => $booking->id,
                    'user_id' => $authenticatedUserId,
                ]);
                $coupon->increment('times_used');
            }

            $booking->load(['car', 'pickupLocation', 'dropoffLocation', 'extras.extra', 'coupon']);

            Mail::to($booking->customer_email)->queue(new BookingCreatedMail($booking));

            return $booking;
        });
    }

    private function attachExtras(Booking $booking, array $extrasPayload, array $quote): void
    {
        if (empty($extrasPayload) || ($quote['extras_subtotal'] ?? 0) <= 0) {
            return;
        }

        $durationUnits = $quote['pricing_mode'] === 'hour' ? $quote['duration']['hours'] : $quote['duration']['days'];

        foreach ($extrasPayload as $extraId => $quantity) {
            $extra = Extra::query()->find($extraId);
            if (! $extra) {
                continue;
            }

            $qty = max(1, (int) $quantity);
            $lineTotal = match ($extra->price_type) {
                'fixed' => (float) $extra->unit_price * $qty,
                'per_hour' => (float) $extra->unit_price * $durationUnits * $qty,
                default => (float) $extra->unit_price * $durationUnits * $qty,
            };

            $booking->extras()->create([
                'extra_id' => $extra->id,
                'quantity' => $qty,
                'unit_price' => $extra->unit_price,
                'line_total' => $lineTotal,
            ]);
        }
    }

    private function generateReference(): string
    {
        return 'TBK-'.Str::upper(Str::random(8));
    }
}
