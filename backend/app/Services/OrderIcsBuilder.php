<?php

namespace App\Services;

use App\Models\GuestHouseBooking;
use App\Models\Order;
use Carbon\Carbon;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class OrderIcsBuilder
{
    public function forOrder(Order $order): string
    {
        $calendar = Calendar::create(config('app.name', 'Rentals').', '.$order->reference)
            ->productIdentifier('-//MyTerraBook//Rental Calendar//EN');

        $pickupName = 'Pick-up: '.($order->car?->name ?? 'Vehicle').' ('.$order->reference.')';
        $calendar->event(
            Event::create($pickupName)
                ->startsAt($order->pickup_at)
                ->endsAt($order->pickup_at->clone()->addHour())
        );

        $dropoffName = 'Drop-off: '.($order->car?->name ?? 'Vehicle').' ('.$order->reference.')';
        $calendar->event(
            Event::create($dropoffName)
                ->startsAt($order->dropoff_at)
                ->endsAt($order->dropoff_at->clone()->addHour())
        );

        return $calendar->toString();
    }

    public function forGuestHouseBooking(GuestHouseBooking $booking): string
    {
        $booking->loadMissing('guestHouse');
        $houseName = $booking->guestHouse?->name ?? 'Guest house stay';
        $reference = $booking->booking_reference;

        $calendar = Calendar::create(config('app.name', 'Rentals').', '.$reference)
            ->productIdentifier('-//MyTerraBook//Stay Calendar//EN');

        $checkIn = Carbon::parse($booking->check_in)->startOfDay()->setTime(15, 0);
        $checkOut = Carbon::parse($booking->check_out)->startOfDay()->setTime(11, 0);

        $calendar->event(
            Event::create('Check-in: '.$houseName.' ('.$reference.')')
                ->startsAt($checkIn)
                ->endsAt($checkIn->clone()->addHour())
        );

        $calendar->event(
            Event::create('Check-out: '.$houseName.' ('.$reference.')')
                ->startsAt($checkOut)
                ->endsAt($checkOut->clone()->addHour())
        );

        return $calendar->toString();
    }
}
