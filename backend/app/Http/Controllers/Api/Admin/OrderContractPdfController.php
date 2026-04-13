<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class OrderContractPdfController extends Controller
{
    public function show(Order $order): Response
    {
        if ($order->order_status !== OrderStatus::Confirmed) {
            abort(404);
        }

        $order->load(['car', 'pickupLocation', 'dropoffLocation']);

        $pdf = Pdf::loadView('pdf.order-contract', ['order' => $order]);

        return $pdf->stream('contract-'.$order->reference.'.pdf');
    }
}
