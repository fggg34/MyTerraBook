<?php

namespace App\Services\Email;

use App\Models\Order;
use App\Support\PaymentEmailSummary;

class OrderEmailPayload
{
    /**
     * @return array<string, string>
     */
    public static function for(Order $order): array
    {
        $order->loadMissing('car');

        $payment = PaymentEmailSummary::forOrder($order);

        return [
            'order_reference' => (string) $order->reference,
            'car_name' => (string) ($order->car?->name ?? 'Vehicle'),
            'customer_name' => (string) $order->customer_name,
            'customer_email' => (string) $order->customer_email,
            'pickup_at' => $order->pickup_at?->format('D, d M Y H:i') ?? '',
            'dropoff_at' => $order->dropoff_at?->format('D, d M Y H:i') ?? '',
            'total' => $payment['total'],
            'total_isk' => $payment['total_isk'],
            'paid_online' => $payment['paid_online'],
            'cash_due_on_arrival' => $payment['cash_due_on_arrival'],
        ];
    }
}
