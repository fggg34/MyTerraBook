<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Services\BookingConfirmationPayloadService;
use Illuminate\Http\JsonResponse;

class BookingConfirmationController extends Controller
{
    public function __construct(
        private readonly BookingConfirmationPayloadService $payloads,
    ) {}

    public function show(string $token): JsonResponse
    {
        $order = Order::query()
            ->where('confirmation_token', $token)
            ->first();

        if ($order) {
            return response()->json([
                'data' => $this->payloads->fromOrder($order),
            ]);
        }

        $booking = GuestHouseBooking::query()
            ->where('confirmation_token', $token)
            ->first();

        if ($booking) {
            return response()->json([
                'data' => $this->payloads->fromGuestHouseBooking($booking),
            ]);
        }

        return response()->json(['message' => 'Confirmation not found.'], 404);
    }
}
