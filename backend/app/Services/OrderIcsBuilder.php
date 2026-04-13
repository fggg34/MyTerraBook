<?php

namespace App\Services;

use App\Models\Order;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

class OrderIcsBuilder
{
    public function forOrder(Order $order): string
    {
        $calendar = Calendar::create(config('app.name', 'Rentals').' — '.$order->reference)
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
}
