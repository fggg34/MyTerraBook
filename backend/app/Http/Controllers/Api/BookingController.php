<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\BookingQuoteRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function quote(BookingQuoteRequest $request, BookingService $bookingService): JsonResponse
    {
        return response()->json($bookingService->quote($request->validated()));
    }

    public function store(StoreBookingRequest $request, BookingService $bookingService): BookingResource
    {
        $booking = $bookingService->create(
            payload: $request->validated(),
            authenticatedUserId: auth()->id()
        );

        return BookingResource::make($booking);
    }
}
