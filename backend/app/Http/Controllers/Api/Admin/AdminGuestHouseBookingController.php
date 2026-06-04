<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\GuestHouseBookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GuestHouseBookingResource;
use App\Models\GuestHouseBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminGuestHouseBookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GuestHouseBooking::query()->with('guestHouse')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('guest_house_id')) {
            $query->where('guest_house_id', (int) $request->query('guest_house_id'));
        }

        if ($request->filled('from')) {
            $query->where('check_in', '>=', $request->string('from'));
        }

        if ($request->filled('to')) {
            $query->where('check_out', '<=', $request->string('to'));
        }

        $bookings = $query->paginate((int) $request->query('per_page', 25));

        return response()->json([
            'data' => GuestHouseBookingResource::collection($bookings),
            'meta' => [
                'current_page' => $bookings->currentPage(),
                'last_page' => $bookings->lastPage(),
                'total' => $bookings->total(),
            ],
        ]);
    }

    public function updateStatus(Request $request, GuestHouseBooking $booking): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(GuestHouseBookingStatus::class)],
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $status = GuestHouseBookingStatus::from($validated['status']);
        $updates = ['status' => $status];

        if ($status === GuestHouseBookingStatus::Confirmed) {
            $updates['confirmed_at'] = now();
        }

        if ($status === GuestHouseBookingStatus::Cancelled) {
            $updates['cancelled_at'] = now();
            $updates['cancellation_reason'] = $validated['cancellation_reason'] ?? 'Cancelled by admin';
        }

        $booking->update($updates);
        $booking->load('guestHouse');

        return response()->json([
            'data' => new GuestHouseBookingResource($booking),
        ]);
    }
}
