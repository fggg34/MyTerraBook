<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookingChangeRequestResource;
use App\Models\BookingChangeRequest;
use App\Services\BookingChangeRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class AdminBookingChangeRequestController extends Controller
{
    public function __construct(
        private readonly BookingChangeRequestService $service,
    ) {}

    public function apply(Request $request, BookingChangeRequest $bookingChangeRequest): JsonResponse
    {
        $data = $request->validate([
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $updated = $this->service->apply(
                $bookingChangeRequest,
                $request->user(),
                $data['admin_response'] ?? null,
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
