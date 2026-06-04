<?php

namespace App\Http\Controllers\Api;

use App\Enums\GuestHouseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreListingReviewRequest;
use App\Http\Resources\Api\ListingReviewResource;
use App\Models\Car;
use App\Models\GuestHouse;
use App\Models\ListingReview;
use Illuminate\Http\JsonResponse;

class ListingReviewController extends Controller
{
    public function indexCar(Car $car): JsonResponse
    {
        abort_unless($car->is_active, 404);

        $reviews = $car->listingReviews()
            ->approved()
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'data' => ListingReviewResource::collection($reviews),
        ]);
    }

    public function storeCar(StoreListingReviewRequest $request, Car $car): JsonResponse
    {
        abort_unless($car->is_active, 404);

        $review = $this->createReview($request, $car);

        return response()->json([
            'data' => new ListingReviewResource($review),
            'message' => 'Thank you — your review has been posted.',
        ], 201);
    }

    public function indexGuestHouse(string $slug): JsonResponse
    {
        $house = $this->resolveGuestHouse($slug);

        $reviews = $house->listingReviews()
            ->approved()
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'data' => ListingReviewResource::collection($reviews),
        ]);
    }

    public function storeGuestHouse(StoreListingReviewRequest $request, string $slug): JsonResponse
    {
        $house = $this->resolveGuestHouse($slug);
        $review = $this->createReview($request, $house);

        return response()->json([
            'data' => new ListingReviewResource($review),
            'message' => 'Thank you — your review has been posted.',
        ], 201);
    }

    private function resolveGuestHouse(string $slug): GuestHouse
    {
        return GuestHouse::query()
            ->where('slug', $slug)
            ->where('status', GuestHouseStatus::Active)
            ->firstOrFail();
    }

    private function createReview(StoreListingReviewRequest $request, Car|GuestHouse $reviewable): ListingReview
    {
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('listing-reviews', 'public');
        }

        $user = $request->user();

        return $reviewable->listingReviews()->create([
            'user_id' => $user?->id,
            'guest_name' => $request->string('guest_name')->trim()->toString(),
            'rating' => (int) $request->input('rating'),
            'body' => $request->string('body')->trim()->toString(),
            'photo_path' => $photoPath,
            'is_approved' => true,
        ]);
    }
}
