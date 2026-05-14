<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class OrderCheckinPdfController extends Controller
{
    public function show(Order $order): Response
    {
        if ($order->order_status !== OrderStatus::Confirmed) {
            abort(404);
        }

        $order->load([
            'car',
            'carUnit',
            'carUnit.distinctiveValues.definition',
            'carUnit.damageMarkers',
            'pickupLocation',
            'dropoffLocation',
        ]);

        $pdf = Pdf::loadView('pdf.order-checkin', ['order' => $order]);

        return $pdf->stream('checkin-'.$order->reference.'.pdf');
    }
}
