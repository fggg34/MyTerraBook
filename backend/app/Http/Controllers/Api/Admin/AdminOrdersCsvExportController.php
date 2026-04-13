<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Support\Money;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminOrdersCsvExportController extends Controller
{
    public function __invoke(): StreamedResponse
    {
        $filename = 'orders-export-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'reference',
                'order_status',
                'customer_email',
                'pickup_at',
                'dropoff_at',
                'total',
                'currency',
            ]);

            Order::query()
                ->with('car')
                ->orderByDesc('id')
                ->chunk(200, function ($orders) use ($out) {
                    foreach ($orders as $order) {
                        fputcsv($out, [
                            $order->reference,
                            $order->order_status->value,
                            $order->customer_email,
                            $order->pickup_at->toIso8601String(),
                            $order->dropoff_at->toIso8601String(),
                            Money::formatDecimalFromCents((int) $order->total_cents),
                            $order->currency,
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
