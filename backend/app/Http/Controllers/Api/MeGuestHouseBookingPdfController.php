<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Admin\GuestHouseBookingPdfController as AdminGuestHouseBookingPdfController;
use App\Http\Controllers\Controller;
use App\Models\GuestHouseBooking;
use App\Services\Documents\RentalTermsPdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MeGuestHouseBookingPdfController extends Controller
{
    public function show(
        Request $request,
        string $ref,
        AdminGuestHouseBookingPdfController $pdfController,
    ): Response {
        $booking = GuestHouseBooking::query()
            ->where('booking_reference', $ref)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return $pdfController->show($booking);
    }

    public function rentalTerms(Request $request, string $ref, RentalTermsPdf $pdf): Response
    {
        $booking = GuestHouseBooking::query()
            ->where('booking_reference', $ref)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return $pdf->stream($booking->booking_reference, $booking->guest_name, $booking->resolvedRentalTermsHtml());
    }
}
