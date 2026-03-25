<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;

class PaymentService
{
    public function createStubPayment(Booking $booking, string $method, float $amount, array $meta = []): Payment
    {
        return $booking->payments()->create([
            'method' => $method,
            'amount' => $amount,
            'status' => 'pending',
            'external_id' => null,
            'meta' => $meta,
        ]);
    }
}
