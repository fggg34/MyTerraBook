<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Email\EmailService;
use App\Services\Email\OrderEmailPayload;

class OrderObserver
{
    public function __construct(
        private readonly EmailService $email,
    ) {}

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status')) {
            return;
        }

        if (! $order->customer_email) {
            return;
        }

        $payload = OrderEmailPayload::for($order);

        match ($order->order_status) {
            OrderStatus::Confirmed => $this->email->send('order_confirmed', $order->customer_email, $payload),
            OrderStatus::Cancelled => $this->email->send('order_cancelled', $order->customer_email, $payload),
            default => null,
        };
    }
}
