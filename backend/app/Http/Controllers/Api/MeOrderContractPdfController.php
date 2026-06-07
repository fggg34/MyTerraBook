<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Admin\OrderContractPdfController as AdminOrderContractPdfController;
use App\Http\Controllers\Controller;
use App\Models\Order;
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
}
