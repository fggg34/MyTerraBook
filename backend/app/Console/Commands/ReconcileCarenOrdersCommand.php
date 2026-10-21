<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\RapydPayment;
use App\Services\Partners\CarenBookingService;
use Illuminate\Console\Command;
use Throwable;

class ReconcileCarenOrdersCommand extends Command
{
    protected $signature = 'caren:reconcile-orders';

    protected $description = 'Retry Caren confirmations/cancellations and release expired unpaid reservations.';

    public function handle(CarenBookingService $caren): int
    {
        $attention = 0;
        Order::where('external_provider', 'caren')->whereNotIn('partner_sync_state', ['confirmed', 'cancelled'])
            ->chunkById(100, function ($orders) use ($caren, &$attention) {
                foreach ($orders as $order) {
                    try {
                        $paid = RapydPayment::where('order_id', $order->id)->where('metadata->order_type', 'car')
                            ->where('status', 'paid')->first();
                        if ($order->partner_sync_state === 'cancel_pending') {
                            $caren->cancel($order);
                        } elseif ($paid) {
                            $caren->confirmPaid($order, $paid);
                        } elseif ($order->order_status === OrderStatus::Pending
                            && in_array($order->partner_sync_state, ['reserved', 'verify_pending'], true)
                            && $order->payment_lock_expires_at?->isPast()) {
                            $caren->cancel($order, onlyIfExpiredAndUnpaid: true);
                        }
                        if (in_array($order->fresh()->partner_sync_state, ['create_unknown', 'payment_unknown', 'checkout_unknown', 'verify_pending'], true)
                            || ($order->updated_at->lt(now()->subMinutes(5)) && in_array($order->partner_sync_state, ['creating', 'checkout_creating', 'payment_recording', 'verifying'], true))) {
                            $this->warn($order->reference.': '.$order->partner_sync_state.' — manual reconciliation required.');
                            $attention++;
                        }
                    } catch (Throwable) {
                        $this->warn($order->reference.': Caren synchronization still pending.');
                        $attention++;
                    }
                }
            });
        $this->info('Caren reconciliation complete; '.$attention.' order(s) need attention.');

        return $attention ? self::FAILURE : self::SUCCESS;
    }
}
