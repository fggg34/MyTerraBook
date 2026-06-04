<?php

namespace App\Http\Controllers\Api;

use App\Enums\GuestHouseBookingStatus;
use App\Enums\GuestHouseCancellationPolicy;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GuestHouseBookingResource;
use App\Models\GuestHouseBooking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeGuestHouseBookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bookings = $request->user()
            ->guestHouseBookings()
            ->with('guestHouse')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => GuestHouseBookingResource::collection($bookings),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, string $ref): JsonResponse
    {
        $booking = GuestHouseBooking::query()
            ->where('booking_reference', $ref)
            ->where('user_id', $request->user()->id)
            ->with('guestHouse')
            ->firstOrFail();

        return response()->json([
            'data' => new GuestHouseBookingResource($booking),
        ]);
    }

    public function cancel(Request $request, string $ref): JsonResponse
    {
        $booking = GuestHouseBooking::query()
            ->where('booking_reference', $ref)
            ->where('user_id', $request->user()->id)
            ->with('guestHouse')
            ->firstOrFail();

        if (! in_array($booking->status, [
            GuestHouseBookingStatus::Pending,
            GuestHouseBookingStatus::Confirmed,
        ], true)) {
            return response()->json(['message' => 'This booking cannot be cancelled.'], 422);
        }

        if (! $this->withinCancellationWindow($booking)) {
            return response()->json([
                'message' => 'Cancellation window has passed for this booking.',
            ], 422);
        }

        $booking->update([
            'status' => GuestHouseBookingStatus::Cancelled,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->input('reason', 'Cancelled by guest'),
        ]);

        return response()->json([
            'data' => new GuestHouseBookingResource($booking->fresh()->load('guestHouse')),
        ]);
    }

    private function withinCancellationWindow(GuestHouseBooking $booking): bool
    {
        $policy = $booking->guestHouse->cancellation_policy;
        $daysBefore = match ($policy) {
            GuestHouseCancellationPolicy::Flexible => 1,
            GuestHouseCancellationPolicy::Moderate => 7,
            GuestHouseCancellationPolicy::Strict => 14,
        };

        return Carbon::parse($booking->check_in)->subDays($daysBefore)->isFuture();
    }
}
