<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Email\OrderEmailNotifier;

class OrderObserver
{
    public function __construct(
        private readonly OrderEmailNotifier $orderEmails,
    ) {}

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('order_status')) {
            return;
        }

        $original = $order->getOriginal('order_status');
        $from = $original instanceof OrderStatus ? $original : OrderStatus::tryFrom((string) $original);
        $to = $order->order_status;

        if ($from === null || $to === null) {
            return;
        }

        $this->orderEmails->notifyStatusChanged($order, $from, $to);
    }
}
