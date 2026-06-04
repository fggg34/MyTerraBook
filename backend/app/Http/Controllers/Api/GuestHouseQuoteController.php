<?php

namespace App\Http\Controllers\Api;

use App\Enums\GuestHouseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GuestHouseQuoteRequest;
use App\Models\GuestHouse;
use App\Services\GuestHouseQuoteService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class GuestHouseQuoteController extends Controller
{
    public function __construct(
        private readonly GuestHouseQuoteService $quoteService,
    ) {}

    public function store(GuestHouseQuoteRequest $request, string $slug): JsonResponse
    {
        $house = GuestHouse::query()
            ->where('slug', $slug)
            ->where('status', GuestHouseStatus::Active)
            ->firstOrFail();

        try {
            $quote = $this->quoteService->quote(
                $house,
                $request->string('check_in')->toString(),
                $request->string('check_out')->toString(),
                $request->integer('guests_count'),
                $request->input('coupon_code'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'data' => array_merge($quote, [
                'guest_house' => [
                    'id' => $house->id,
                    'slug' => $house->slug,
                    'name' => $house->name,
                ],
            ]),
        ]);
    }
}
