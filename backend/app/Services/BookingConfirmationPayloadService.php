<?php

namespace App\Services;

use App\Models\GuestHouseBooking;
use App\Models\Order;
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function fromGuestHouseBooking(GuestHouseBooking $booking): array
    {
        $booking->loadMissing('guestHouse');

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
        ];
    }
}
