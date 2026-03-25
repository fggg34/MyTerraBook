<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;

class MeBookingController extends Controller
{
    public function index()
    {
        $bookings = auth()->user()
            ->bookings()
            ->with(['car', 'pickupLocation', 'dropoffLocation', 'extras.extra', 'coupon'])
            ->latest()
            ->paginate(10);

        return BookingResource::collection($bookings);
    }
}
