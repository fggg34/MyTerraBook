<?php

namespace App\Support;

use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Models\RapydPayment;

/**
 * Build paid-online / cash-on-arrival summary fields for confirmation emails.
 * Amounts are always formatted in ISK (zero-decimal).
 */
final class PaymentEmailSummary
{
    /**
     * @return array{total_isk: string, paid_online: string, cash_due_on_arrival: string, total: string}
     */
    public static function forGuestHouseBooking(GuestHouseBooking $booking): array
    {
        $payment = RapydPayment::query()
            ->where('order_id', $booking->id)
            ->where(function ($q) {
                $q->whereNull('metadata->order_type')
                    ->orWhere('metadata->order_type', 'guesthouse');
            })
            ->latest()
            ->first();

        if ($payment) {
            return self::fromAmounts(
                (float) $payment->total_price,
                (float) $payment->platform_fee,
                (float) $payment->cash_due_on_arrival,
            );
        }

        if ($booking->platform_fee > 0 || $booking->cash_due_on_arrival > 0) {
            $total = (float) ($booking->total_price ?: $booking->total_amount / 100);

            return self::fromAmounts($total, (float) $booking->platform_fee, (float) $booking->cash_due_on_arrival);
        }

        return self::fromTotalMajor((float) ($booking->total_price ?: $booking->total_amount / 100));
    }

    /**
     * @return array{total_isk: string, paid_online: string, cash_due_on_arrival: string, total: string}
     */
    public static function forOrder(Order $order): array
    {
        $payment = RapydPayment::query()
            ->where('order_id', $order->id)
            ->where(function ($q) {
                $q->whereNull('metadata->order_type')
                    ->orWhere('metadata->order_type', 'car');
            })
            ->latest()
            ->first();

        if ($payment) {
            return self::fromAmounts(
                (float) $payment->total_price,
                (float) $payment->platform_fee,
                (float) $payment->cash_due_on_arrival,
            );
        }

        return self::fromTotalMajor($order->total_cents / 100);
    }

    /**
     * @return array{total_isk: string, paid_online: string, cash_due_on_arrival: string, total: string}
     */
    public static function fromAmounts(float $total, float $platformFee, float $cashDue): array
    {
        $totalIsk = Money::formatIsk($total);
        $paidIsk = Money::formatIsk($platformFee);
        $cashIsk = Money::formatIsk($cashDue);

        return [
            'total_isk' => $totalIsk,
            'paid_online' => $paidIsk,
            'cash_due_on_arrival' => $cashIsk,
            'total' => $totalIsk,
        ];
    }

    /**
     * @return array{total_isk: string, paid_online: string, cash_due_on_arrival: string, total: string}
     */
    private static function fromTotalMajor(float $total): array
    {
        $rate = (float) config('rapyd.commission_rate', 0.15);
        $platformFee = round($total * $rate, 0);
        $cashDue = round($total - $platformFee, 0);

        return self::fromAmounts($total, $platformFee, $cashDue);
    }
}
