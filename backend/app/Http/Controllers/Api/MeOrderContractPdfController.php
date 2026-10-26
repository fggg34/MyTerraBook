<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Admin\OrderContractPdfController as AdminOrderContractPdfController;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Documents\RentalTermsPdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MeOrderContractPdfController extends Controller
{
    public function show(Request $request, Order $order, AdminOrderContractPdfController $pdfController): Response
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        return $pdfController->show($order);
    }

    public function rentalTerms(Request $request, Order $order, RentalTermsPdf $pdf): Response
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403);
        }

        return $pdf->stream($order->reference, $order->customer_name, $order->resolvedRentalTermsHtml());
    }
}
