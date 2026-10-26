<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Services\BookingConfirmationPayloadService;
use App\Services\Documents\RentalTermsPdf;
use App\Services\OrderIcsBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class BookingConfirmationController extends Controller
{
    public function __construct(
        private readonly BookingConfirmationPayloadService $payloads,
        private readonly OrderIcsBuilder $icsBuilder,
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

    public function calendar(string $token): Response
    {
        $order = Order::query()
            ->where('confirmation_token', $token)
            ->first();

        if ($order) {
            $order->load('car');

            return response($this->icsBuilder->forOrder($order), 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="booking-'.$order->reference.'.ics"',
            ]);
        }

        $booking = GuestHouseBooking::query()
            ->where('confirmation_token', $token)
            ->first();

        if ($booking) {
            return response($this->icsBuilder->forGuestHouseBooking($booking), 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="booking-'.$booking->booking_reference.'.ics"',
            ]);
        }

        abort(404);
    }

    public function rentalTerms(string $token, RentalTermsPdf $pdf): SymfonyResponse
    {
        $order = Order::query()
            ->where('confirmation_token', $token)
            ->first();

        if ($order) {
            return $pdf->stream($order->reference, $order->customer_name, $order->resolvedRentalTermsHtml());
        }

        $booking = GuestHouseBooking::query()
            ->where('confirmation_token', $token)
            ->first();

        if ($booking) {
            return $pdf->stream($booking->booking_reference, $booking->guest_name, $booking->resolvedRentalTermsHtml());
        }

        abort(404);
    }
}
