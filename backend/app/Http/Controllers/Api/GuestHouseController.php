<?php

namespace App\Http\Controllers\Api;

use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GuestHouseDetailResource;
use App\Http\Resources\Api\GuestHouseListResource;
use App\Models\GuestHouse;
use App\Services\GuestHouseAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuestHouseController extends Controller
{
    public function __construct(
        private readonly GuestHouseAvailabilityService $availabilityService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = GuestHouse::query()
            ->active()
            ->with(['images', 'amenities']);

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->string('city').'%');
        }

        if ($request->filled('type')) {
            $type = GuestHouseType::tryFrom($request->string('type')->toString());
            if ($type !== null) {
                $query->where('type', $type);
            }
        }

        if ($request->filled('guests')) {
            $query->where('max_guests', '>=', (int) $request->query('guests'));
        }

        if ($request->filled('min_price')) {
            $query->where('base_price_per_night', '>=', (int) $request->query('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('base_price_per_night', '<=', (int) $request->query('max_price'));
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            $query->available(
                $request->string('check_in')->toString(),
                $request->string('check_out')->toString(),
            );
        }

        $houses = $query->orderBy('name')->paginate((int) $request->query('per_page', 12));

        return response()->json([
            'data' => GuestHouseListResource::collection($houses),
            'meta' => [
                'current_page' => $houses->currentPage(),
                'last_page' => $houses->lastPage(),
                'per_page' => $houses->perPage(),
                'total' => $houses->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $house = GuestHouse::query()
            ->where('slug', $slug)
            ->where('status', GuestHouseStatus::Active)
            ->with(['images', 'amenities', 'seasonalPrices', 'reviews'])
            ->firstOrFail();

        return response()->json([
            'data' => new GuestHouseDetailResource($house),
        ]);
    }

    public function availability(Request $request, string $slug): JsonResponse
    {
        $house = GuestHouse::query()
            ->where('slug', $slug)
            ->where('status', GuestHouseStatus::Active)
            ->firstOrFail();

        $from = $request->query('from', now()->toDateString());
        $to = $request->query('to', now()->addMonths(3)->toDateString());

        $blocked = $this->availabilityService->getBlockedDates($house, $from, $to);

        return response()->json([
            'data' => [
                'blocked_dates' => $blocked,
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }
}
