<?php

namespace App\Services\Email;

use App\Models\Order;
use App\Support\Money;

class OrderEmailPayload
{
    /**
     * @return array<string, string>
     */
    public static function for(Order $order): array
    {
        $order->loadMissing('car');

        return [
            'order_reference' => (string) $order->reference,
            'car_name' => (string) ($order->car?->name ?? 'Vehicle'),
            'customer_name' => (string) $order->customer_name,
            'customer_email' => (string) $order->customer_email,
            'pickup_at' => $order->pickup_at?->format('D, d M Y H:i') ?? '',
            'dropoff_at' => $order->dropoff_at?->format('D, d M Y H:i') ?? '',
            'total' => Money::formatDecimalFromCents((int) $order->total_cents).' '.strtoupper((string) $order->currency),
        ];
    }
}
