<?php

namespace App\Services;

use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Models\RapydPayment;
use App\Models\User;
use App\Support\Money;
use App\Support\VehicleType;

class BookingConfirmationPayloadService
{
    /**
     * @return array<string, mixed>
     */
    public function fromOrder(Order $order): array
    {
        $order->loadMissing([
            'car.subCategory.mainCategory',
            'car.host',
            'pickupLocation',
            'dropoffLocation',
            'priceType',
            'rentalOptions',
        ]);

        $car = $order->car;
        $pickup = $order->pickup_at;
        $dropoff = $order->dropoff_at;
        $nights = max(1, (int) $pickup->copy()->startOfDay()->diffInDays($dropoff->copy()->startOfDay()));
        $vehicleType = VehicleType::fromSubCategory($car?->subCategory);

        return [
            'bookable_kind' => 'order',
            'booking_type' => $vehicleType,
            'confirmation_token' => $order->confirmation_token,
            'confirmation_url' => $order->confirmation_url,
            'reference' => $order->reference,
            'order_id' => $order->id,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'total' => Money::formatDecimalFromCents((int) $order->total_cents),
            'total_formatted' => Money::formatDecimalFromCents((int) $order->total_cents).' '.$order->currency,
            'currency' => $order->currency,
            'nights' => $nights,
            'pickup_at' => $pickup->toIso8601String(),
            'dropoff_at' => $dropoff->toIso8601String(),
            'pickup_time' => $pickup->format('H:i'),
            'dropoff_time' => $dropoff->format('H:i'),
            'pickup_location_id' => $order->pickup_location_id,
            'dropoff_location_id' => $order->dropoff_location_id,
            'pickup_location_name' => $order->pickupLocation?->name,
            'dropoff_location_name' => $order->dropoffLocation?->name,
            'same_return' => (int) $order->pickup_location_id === (int) $order->dropoff_location_id,
            'rental_option_ids' => $order->rentalOptions->pluck('rental_option_id')->all(),
            'price_type' => $order->priceType ? [
                'id' => $order->priceType->id,
                'name' => $order->priceType->name,
                'slug' => $order->priceType->slug,
                'attribute_value_per_day' => $order->priceType->attribute_value_per_day,
            ] : null,
            'item' => $car ? [
                'id' => $car->id,
                'name' => $car->name,
                'slug' => $car->slug,
                'main_image_path' => $car->main_image_path,
                'units_available' => $car->units_available,
                'transmission' => $car->transmission,
                'category' => $car->subCategory ? ['name' => $car->subCategory->name] : null,
            ] : null,
            'host' => $this->hostPayload($car?->host),
            'payment' => $this->rapydPaymentPayload($order->id, 'car'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function fromGuestHouseBooking(GuestHouseBooking $booking): array
    {
        $booking->loadMissing('guestHouse.host');

        $house = $booking->guestHouse;

        return [
            'bookable_kind' => 'guesthouse',
            'booking_type' => 'guesthouse',
            'confirmation_token' => $booking->confirmation_token,
            'confirmation_url' => $booking->confirmation_url,
            'reference' => $booking->booking_reference,
            'order_id' => null,
            'customer_name' => $booking->guest_name,
            'customer_email' => $booking->guest_email,
            'total' => Money::formatDecimalFromCents((int) $booking->total_amount),
            'total_formatted' => '€ '.Money::formatDecimalFromCents((int) $booking->total_amount),
            'currency' => 'EUR',
            'nights' => $booking->nights,
            'check_in' => $booking->check_in->toDateString(),
            'check_out' => $booking->check_out->toDateString(),
            'guests_count' => $booking->guests_count,
            'item' => $house ? [
                'id' => $house->id,
                'name' => $house->name,
                'slug' => $house->slug,
                'city' => $house->city,
                'thumbnail' => $house->thumbnail,
                'check_in_time' => $house->check_in_time,
                'check_out_time' => $house->check_out_time,
            ] : null,
            'host' => $this->hostPayload($house?->host),
            'payment' => $this->rapydPaymentPayload($booking->id, 'guesthouse'),
        ];
    }

    /**
     * The online-fee / cash-on-arrival split from the Rapyd payment (if any),
     * so the existing confirmation page can display it dynamically.
     *
     * @return array<string, mixed>|null
     */
    private function rapydPaymentPayload(int $orderId, string $orderType): ?array
    {
        $payment = RapydPayment::query()
            ->where('order_id', $orderId)
            ->where(function ($q) use ($orderType) {
                $q->whereNull('metadata->order_type')
                    ->orWhere('metadata->order_type', $orderType);
            })
            ->latest()
            ->first();

        if ($payment === null) {
            return null;
        }

        return [
            'method' => 'rapyd_card',
            'status' => $payment->status, // pending | paid | failed | refunded
            'currency' => $payment->currency,
            'total_price' => (float) $payment->total_price,
            'platform_fee' => (float) $payment->platform_fee,
            'cash_due_on_arrival' => (float) $payment->cash_due_on_arrival,
            'paid_at' => $payment->paid_at?->toIso8601String(),
        ];
    }

    /**
     * @return array{name: string, member_since: ?string, initial: string}|null
     */
    private function hostPayload(?User $host): ?array
    {
        if ($host === null) {
            return null;
        }

        $name = trim((string) $host->name);
        $initial = strtoupper(substr($name, 0, 1) ?: 'H');

        return [
            'name' => $name !== '' ? $name : 'Your host',
            'member_since' => $host->created_at?->toDateString(),
            'initial' => $initial,
        ];
    }
}
