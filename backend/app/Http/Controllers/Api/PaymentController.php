<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StorePaymentRequest;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Booking $booking, PaymentService $paymentService): JsonResponse
    {
        $payment = $paymentService->createStubPayment(
            booking: $booking,
            method: $request->string('method')->toString(),
            amount: (float) $request->input('amount', $booking->total),
            meta: (array) $request->input('meta', []),
        );

        return response()->json(['message' => 'Payment stub recorded.', 'payment' => $payment], 201);
    }
}
