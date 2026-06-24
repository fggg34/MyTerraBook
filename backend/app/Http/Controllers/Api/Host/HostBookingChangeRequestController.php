<?php

namespace App\Http\Controllers\Api\Host;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookingChangeRequestResource;
use App\Models\BookingChangeRequest;
use App\Models\Order;
use App\Services\BookingChangeRequestService;
use App\Services\Host\HostOrderModificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class HostBookingChangeRequestController extends Controller
{
    public function __construct(
        private readonly BookingChangeRequestService $service,
        private readonly HostOrderModificationService $orderModifications,
    ) {}

    public function apply(Request $request, BookingChangeRequest $bookingChangeRequest): JsonResponse
    {
        $this->authorize('review', $bookingChangeRequest);

        $data = $request->validate([
            'admin_response' => ['nullable', 'string', 'max:5000'],
            'requested_changes' => ['nullable', 'array'],
            'requested_changes.price_type_id' => ['nullable', 'integer'],
            'requested_changes.rental_options' => ['nullable', 'array'],
            'requested_changes.rental_options.*' => ['integer'],
            'requested_changes.pickup_at' => ['nullable', 'date'],
            'requested_changes.dropoff_at' => ['nullable', 'date'],
        ]);

        $changesOverride = null;
        $bookable = $bookingChangeRequest->bookable;
        if (! empty($data['requested_changes']) && $bookable instanceof Order) {
            $changesOverride = $this->orderModifications->mergeChanges(
                $bookable,
                $bookingChangeRequest->requested_changes ?? [],
                $data['requested_changes'],
            );
        }

        try {
            $updated = $this->service->apply(
                $bookingChangeRequest,
                $request->user(),
                $data['admin_response'] ?? null,
                $changesOverride,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Modification applied and booking updated.',
            'data' => new BookingChangeRequestResource($updated),
        ]);
    }

    public function reject(Request $request, BookingChangeRequest $bookingChangeRequest): JsonResponse
    {
        $this->authorize('review', $bookingChangeRequest);

        $data = $request->validate([
            'admin_response' => ['required', 'string', 'max:5000'],
        ]);

        try {
            $updated = $this->service->reject(
                $bookingChangeRequest,
                $request->user(),
                $data['admin_response'],
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Request rejected.',
            'data' => new BookingChangeRequestResource($updated),
        ]);
    }
}
