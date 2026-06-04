<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\GuestHouseBookingStatus;
use App\Http\Controllers\Controller;
use App\Models\GuestHouseBooking;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class GuestHouseBookingPdfController extends Controller
{
    public function show(GuestHouseBooking $booking): Response
    {
        if (! in_array($booking->status, [
            GuestHouseBookingStatus::Confirmed,
            GuestHouseBookingStatus::Completed,
        ], true)) {
            abort(404);
        }

        $booking->load('guestHouse');
        $currency = Setting::getValue('shop.currency', ['code' => 'EUR'])['code'] ?? 'EUR';

        $pdf = Pdf::loadView('pdf.guest-house-booking', [
            'booking' => $booking,
            'currency' => $currency,
        ]);

        return $pdf->stream('booking-'.$booking->booking_reference.'.pdf');
    }
}
