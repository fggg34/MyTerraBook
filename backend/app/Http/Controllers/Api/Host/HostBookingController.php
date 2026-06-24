<?php

namespace App\Http\Controllers\Api\Host;

use App\Http\Controllers\Api\Admin\GuestHouseBookingPdfController;
use App\Http\Controllers\Api\Admin\OrderContractPdfController;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GuestHouseBookingResource;
use App\Http\Resources\Api\Host\HostGuestHouseBookingDetailResource;
use App\Http\Resources\Api\Host\HostOrderDetailResource;
use App\Http\Resources\Api\OrderResource;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Services\Host\HostOrderModificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class HostBookingController extends Controller
{
    public function __construct(
        private readonly HostOrderModificationService $orderModifications,
    ) {}

    public function carOrders(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $carIds = Car::query()->where('user_id', $request->user()->id)->pluck('id');

        $query = Order::query()
            ->whereIn('car_id', $carIds)
            ->with(['car', 'pickupLocation', 'dropoffLocation'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('order_status', $request->string('status'));
        }

        $orders = $query->paginate((int) $request->query('per_page', 25));

        return response()->json([
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function showCarOrder(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load([
            'car.subCategory',
            'car.rentalOptions',
            'pickupLocation',
            'dropoffLocation',
            'priceType',
            'lineItems',
            'rentalOptions.rentalOption',
            'changeRequests',
        ]);

        return response()->json([
            'data' => new HostOrderDetailResource($order),
        ]);
    }

    public function previewCarOrderModification(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'price_type_id' => ['nullable', 'integer'],
            'rental_options' => ['nullable', 'array'],
            'rental_options.*' => ['integer'],
            'pickup_at' => ['nullable', 'date'],
            'dropoff_at' => ['nullable', 'date'],
        ]);

        try {
            $preview = $this->orderModifications->preview($order, $data);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $preview]);
    }

    public function updateCarOrder(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $data = $request->validate([
            'price_type_id' => ['nullable', 'integer'],
            'rental_options' => ['nullable', 'array'],
            'rental_options.*' => ['integer'],
            'pickup_at' => ['nullable', 'date'],
            'dropoff_at' => ['nullable', 'date'],
        ]);

        try {
            $updated = $this->orderModifications->apply($order, $data);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Booking updated.',
            'data' => new HostOrderDetailResource($updated),
        ]);
    }

    public function showGuestHouseBooking(Request $request, GuestHouseBooking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        $booking->load([
            'guestHouse',
            'changeRequests',
        ]);

        return response()->json([
            'data' => new HostGuestHouseBookingDetailResource($booking),
        ]);
    }

    public function guestHouseBookings(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GuestHouseBooking::class);

        $houseIds = GuestHouse::query()->where('user_id', $request->user()->id)->pluck('id');

        $query = GuestHouseBooking::query()
            ->whereIn('guest_house_id', $houseIds)
            ->with('guestHouse')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
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

    public function carContractPdf(Order $order, OrderContractPdfController $pdfController)
    {
        $this->authorize('view', $order);

        return $pdfController->show($order);
    }

    public function guestHouseContractPdf(GuestHouseBooking $booking, GuestHouseBookingPdfController $pdfController)
    {
        $this->authorize('view', $booking);

        return $pdfController->show($booking);
    }
}
