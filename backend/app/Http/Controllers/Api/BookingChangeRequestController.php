<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BookingChangeRequestResource;
use App\Models\BookingChangeRequest;
use App\Services\BookingChangeRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class BookingChangeRequestController extends Controller
{
    public function __construct(
        private readonly BookingChangeRequestService $service,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bookable_kind' => ['required', 'in:order,guesthouse'],
            'reference' => ['required', 'string', 'max:64'],
            'customer_email' => ['required', 'email', 'max:255'],
            'type' => ['required', 'in:modification,cancellation'],
            'customer_message' => ['required', 'string', 'max:5000'],
            'requested_changes' => ['nullable', 'array'],
            'requested_changes.pickup_at' => ['nullable', 'date'],
            'requested_changes.dropoff_at' => ['nullable', 'date'],
            'requested_changes.pickup_location_id' => ['nullable', 'integer'],
            'requested_changes.dropoff_location_id' => ['nullable', 'integer'],
            'requested_changes.price_type_id' => ['nullable', 'integer'],
            'requested_changes.rental_options' => ['nullable', 'array'],
            'requested_changes.rental_options.*' => ['integer'],
            'requested_changes.check_in' => ['nullable', 'date'],
            'requested_changes.check_out' => ['nullable', 'date'],
            'requested_changes.guests_count' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            $bookable = $this->service->resolveBookable(
                $data['bookable_kind'],
                $data['reference'],
                $data['customer_email'],
            );
            $changeRequest = $this->service->create($bookable, $request->user(), $data);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Your request has been sent to the host team for review.',
            'data' => new BookingChangeRequestResource($changeRequest),
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $data = $request->validate([
            'bookable_kind' => ['required', 'in:order,guesthouse'],
            'reference' => ['required', 'string', 'max:64'],
            'customer_email' => [$request->user() ? 'nullable' : 'required', 'email', 'max:255'],
        ]);

        try {
            $bookable = $this->service->resolveBookable(
                $data['bookable_kind'],
                $data['reference'],
                $data['customer_email'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        $user = $request->user();
        if ($user && ! $this->service->userCanAccessBookable($user, $bookable)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }
        if (! $user && empty($data['customer_email'])) {
            return response()->json(['message' => 'Customer email is required.'], 422);
        }

        $requests = BookingChangeRequest::query()
            ->where('bookable_type', $bookable::class)
            ->where('bookable_id', $bookable->getKey())
            ->latest()
            ->get();

        return response()->json([
            'data' => BookingChangeRequestResource::collection($requests),
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'requested_changes' => ['required', 'array'],
            'requested_changes.pickup_at' => ['nullable', 'date'],
            'requested_changes.dropoff_at' => ['nullable', 'date'],
            'requested_changes.pickup_location_id' => ['nullable', 'integer'],
            'requested_changes.dropoff_location_id' => ['nullable', 'integer'],
            'requested_changes.price_type_id' => ['nullable', 'integer'],
            'requested_changes.rental_options' => ['nullable', 'array'],
            'requested_changes.rental_options.*' => ['integer'],
        ]);

        $order = \App\Models\Order::query()->findOrFail($data['order_id']);
        $user = $request->user();
        if ($user && ! $this->service->userCanAccessBookable($user, $order)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        try {
            $preview = $this->service->previewOrderModification($order, $data['requested_changes']);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $preview]);
    }
}
