<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminBookingRequest;
use App\Http\Requests\Admin\UpdateBookingStatusRequest;
use App\Http\Resources\BookingResource;
use App\Mail\BookingStatusUpdatedMail;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    public function index()
    {
        return BookingResource::collection(
            Booking::query()
                ->with(['car', 'pickupLocation', 'dropoffLocation', 'coupon'])
                ->latest()
                ->paginate(20)
        );
    }

    public function store(StoreAdminBookingRequest $request, BookingService $bookingService)
    {
        $booking = $bookingService->create(
            payload: $request->validated(),
            authenticatedUserId: $request->integer('user_id') ?: null,
            adminCreatorId: auth()->id()
        );

        return BookingResource::make($booking);
    }

    public function show(Booking $booking)
    {
        return BookingResource::make($booking->load(['car', 'pickupLocation', 'dropoffLocation', 'extras.extra', 'coupon']));
    }

    public function update(UpdateBookingStatusRequest $request, Booking $booking)
    {
        $oldStatus = $booking->status;
        $booking->update([
            'status' => BookingStatus::from($request->string('status')->toString()),
            'notes' => $request->input('notes', $booking->notes),
        ]);

        if ($oldStatus !== $booking->status) {
            Mail::to($booking->customer_email)->queue(new BookingStatusUpdatedMail($booking));
        }

        return BookingResource::make($booking);
    }

    public function destroy(Booking $booking)
    {
        $booking->delete();

        return response()->noContent();
    }
}
