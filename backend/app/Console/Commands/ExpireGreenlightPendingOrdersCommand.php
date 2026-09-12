<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Partners\GreenlightSettings;
use Illuminate\Console\Command;

class ExpireGreenlightPendingOrdersCommand extends Command
{
    protected $signature = 'greenlight:expire-pending';

    protected $description = 'Cancel unpaid Greenlight holds after the MyTerra payment lock expires.';

    public function handle(): int
    {
        $orders = Order::query()
            ->where('external_provider', GreenlightSettings::PROVIDER)
            ->where('order_status', OrderStatus::Pending)
            ->whereNotNull('external_reference')
            ->whereNotNull('payment_lock_expires_at')
            ->where('payment_lock_expires_at', '<', now())
            ->get();

        foreach ($orders as $order) {
            $order->transitionOrderStatus(OrderStatus::Cancelled);
        }

        $this->info('Released '.$orders->count().' expired Greenlight hold'.($orders->count() === 1 ? '' : 's').'.');

        return self::SUCCESS;
    }
}
