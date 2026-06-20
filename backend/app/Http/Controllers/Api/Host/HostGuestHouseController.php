<?php

namespace App\Http\Controllers\Api\Host;

use App\Enums\GuestHouseCancellationPolicy;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Host\HostGuestHouseResource;
use App\Models\GuestHouse;
use App\Models\GuestHouseAvailabilityBlock;
use App\Models\GuestHouseImage;
use App\Models\GuestHouseRoomDetail;
use App\Models\GuestHouseSeasonalPrice;
use App\Support\HostPricingValidation;
use App\Services\Email\EmailService;
use App\Services\ListingSeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HostGuestHouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', GuestHouse::class);

        $houses = GuestHouse::query()
            ->where('user_id', $request->user()->id)
            ->withCount('bookings')
            ->orderByDesc('updated_at')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'data' => HostGuestHouseResource::collection($houses),
            'meta' => [
                'current_page' => $houses->currentPage(),
                'last_page' => $houses->lastPage(),
                'total' => $houses->total(),
            ],
        ]);
    }

    public function store(Request $request, ListingSeoService $seo): JsonResponse
    {
        $this->authorize('create', GuestHouse::class);

        $data = $this->validatedData($request);
        $houseData = collect($data)->except(['amenity_ids', 'seasonal_prices'])->all();
        $house = GuestHouse::query()->create([
            'type' => GuestHouseType::Apartment,
            'city' => 'Reykjavík',
            'country' => 'Iceland',
            'max_guests' => 2,
            'bedrooms' => 1,
            'bathrooms' => 1,
            'beds' => 1,
            'min_nights' => 1,
            'base_price_per_night' => 10000,
            ...$houseData,
            'user_id' => $request->user()->id,
            'status' => GuestHouseStatus::Draft,
        ]);

        if ($request->has('amenity_ids')) {
            $house->amenities()->sync($request->input('amenity_ids', []));
        }

        if ($request->has('seasonal_prices')) {
            $this->syncSeasonalPrices($house, $request->input('seasonal_prices', []));
        }

        if ($request->has('room_details')) {
            $this->syncRoomDetails($house, $request->input('room_details', []));
        }

        $seo->syncGuestHouse($house);
        $this->loadHostRelations($house);

        return response()->json(['data' => new HostGuestHouseResource($house)], 201);
    }

    public function show(GuestHouse $guestHouse): JsonResponse
    {
        $this->authorize('view', $guestHouse);

        $this->loadHostRelations($guestHouse);

        return response()->json(['data' => new HostGuestHouseResource($guestHouse)]);
    }

    public function update(Request $request, GuestHouse $guestHouse, ListingSeoService $seo): JsonResponse
    {
        $this->authorize('update', $guestHouse);

        $data = $this->validatedData($request, $guestHouse);
        unset($data['status'], $data['amenity_ids'], $data['seasonal_prices'], $data['room_details']);
        $guestHouse->update($data);

        if ($request->has('amenity_ids')) {
            $guestHouse->amenities()->sync($request->input('amenity_ids', []));
        }

        if ($request->has('seasonal_prices')) {
            $this->syncSeasonalPrices($guestHouse, $request->input('seasonal_prices', []));
        }

        if ($request->has('room_details')) {
            $this->syncRoomDetails($guestHouse, $request->input('room_details', []));
        }

        $seo->syncGuestHouse($guestHouse);
        $this->loadHostRelations($guestHouse);

        return response()->json(['data' => new HostGuestHouseResource($guestHouse)]);
    }

    public function destroy(GuestHouse $guestHouse): JsonResponse
    {
        $this->authorize('delete', $guestHouse);
        $guestHouse->delete();

        return response()->json(['message' => 'Guest house deleted.']);
    }

    public function submit(GuestHouse $guestHouse, EmailService $email): JsonResponse
    {
        $this->authorize('submit', $guestHouse);

        if (! in_array($guestHouse->status, [GuestHouseStatus::Draft, GuestHouseStatus::Rejected], true)) {
            return response()->json(['message' => 'Only draft or rejected listings can be submitted.'], 422);
        }

        $address = trim((string) $guestHouse->address);
        $city = trim((string) $guestHouse->city);
        $country = trim((string) $guestHouse->country);

        if ($address === '' || strlen($address) < 5) {
            return response()->json(['message' => 'A complete street address is required before submitting for review.'], 422);
        }

        if ($city === '') {
            return response()->json(['message' => 'City is required before submitting for review.'], 422);
        }

        if ($country === '') {
            return response()->json(['message' => 'Country is required before submitting for review.'], 422);
        }

        if ((int) $guestHouse->base_price_per_night <= 0) {
            return response()->json(['message' => 'Set a nightly price greater than zero before submitting for review.'], 422);
        }

        $hasMainImage = trim((string) $guestHouse->thumbnail) !== '';
        if (! $hasMainImage) {
            return response()->json(['message' => 'A main image is required before submitting for review.'], 422);
        }

        if ($guestHouse->images()->count() < 5) {
            return response()->json(['message' => 'At least 5 detail photos are required before submitting for review.'], 422);
        }

        if (! $guestHouse->amenities()->exists()) {
            return response()->json(['message' => 'Select at least one amenity before submitting for review.'], 422);
        }

        if ((int) $guestHouse->max_guests < 1) {
            return response()->json(['message' => 'Max guests (sleeps) is required before submitting for review.'], 422);
        }

        if ($guestHouse->bedrooms === null || (int) $guestHouse->bedrooms < 0) {
            return response()->json(['message' => 'Bedrooms is required before submitting for review.'], 422);
        }

        if ((int) $guestHouse->bathrooms < 1) {
            return response()->json(['message' => 'Bathrooms must be at least 1 before submitting for review.'], 422);
        }

        if (trim((string) $guestHouse->city) === '') {
            return response()->json(['message' => 'City is required before submitting for review.'], 422);
        }

        $guestHouse->update([
            'status' => GuestHouseStatus::PendingReview,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        $guestHouse->loadMissing('host');
        if ($hostEmail = $guestHouse->host?->email) {
            $email->send('listing_submitted', $hostEmail, [
                'host_name' => $guestHouse->host?->name,
                'listing_name' => $guestHouse->name,
            ]);
        }

        $guestHouse = $guestHouse->fresh();
        $this->loadHostRelations($guestHouse);

        return response()->json(['data' => new HostGuestHouseResource($guestHouse)]);
    }

    public function uploadImages(Request $request, GuestHouse $guestHouse, ListingSeoService $seo): JsonResponse
    {
        $this->authorize('update', $guestHouse);

        $request->validate([
            'thumbnail' => ['nullable', 'image', 'max:8192'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:8192'],
            'gallery_order' => ['nullable', 'array'],
            'gallery_order.*' => ['integer'],
            'room_detail_id' => ['nullable', 'integer'],
            'room_detail_image' => ['nullable', 'image', 'max:8192'],
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('guesthouses/thumbnails', 'public');
            $guestHouse->update(['thumbnail' => $path]);
        }

        if ($request->hasFile('gallery')) {
            $maxOrder = $guestHouse->images()->max('sort_order') ?? -1;
            foreach ($request->file('gallery') as $file) {
                $maxOrder++;
                $path = $file->store('guesthouses/gallery', 'public');
                GuestHouseImage::query()->create([
                    'guest_house_id' => $guestHouse->id,
                    'path' => $path,
                    'sort_order' => $maxOrder,
                ]);
            }
        }

        if ($request->filled('gallery_order')) {
            foreach ($request->input('gallery_order') as $index => $imageId) {
                GuestHouseImage::query()
                    ->where('guest_house_id', $guestHouse->id)
                    ->whereKey($imageId)
                    ->update(['sort_order' => $index]);
            }
        }

        if ($request->hasFile('room_detail_image')) {
            if (! Schema::hasTable('guest_house_room_details')) {
                return response()->json(['message' => 'Room details are unavailable until database migrations have been applied.'], 503);
            }

            $request->validate([
                'room_detail_id' => ['required', 'integer'],
            ]);

            $detail = GuestHouseRoomDetail::query()
                ->where('guest_house_id', $guestHouse->id)
                ->whereKey($request->integer('room_detail_id'))
                ->firstOrFail();

            if ($detail->image_path) {
                Storage::disk('public')->delete($detail->image_path);
            }

            $path = $request->file('room_detail_image')->store('guesthouses/room-details', 'public');
            $detail->update(['image_path' => $path]);
        }

        if ($request->hasFile('thumbnail')) {
            $seo->syncGuestHouse($guestHouse);
        }

        $this->loadHostMediaRelations($guestHouse);

        return response()->json(['data' => new HostGuestHouseResource($guestHouse)]);
    }

    public function deleteImage(GuestHouse $guestHouse, GuestHouseImage $image): JsonResponse
    {
        $this->authorize('update', $guestHouse);
        abort_unless($image->guest_house_id === $guestHouse->id, 404);

        if ($guestHouse->thumbnail === $image->path) {
            $guestHouse->update(['thumbnail' => null]);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(['message' => 'Image deleted.']);
    }

    public function availabilityBlocks(GuestHouse $guestHouse): JsonResponse
    {
        $this->authorize('view', $guestHouse);

        $blocks = $guestHouse->availabilityBlocks()->orderBy('blocked_from')->get();

        return response()->json(['data' => $blocks]);
    }

    public function storeAvailabilityBlock(Request $request, GuestHouse $guestHouse): JsonResponse
    {
        $this->authorize('update', $guestHouse);

        $data = $request->validate([
            'blocked_from' => ['required', 'date'],
            'blocked_to' => ['required', 'date', 'after_or_equal:blocked_from'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'blocked_to.after_or_equal' => 'The end date must be on or after the start date.',
        ], [
            'blocked_from' => 'start date',
            'blocked_to' => 'end date',
        ]);

        HostPricingValidation::assertNotPastDate('blocked_from', $data['blocked_from']);

        $overlapping = $guestHouse->availabilityBlocks()
            ->where('source', 'manual')
            ->get()
            ->first(fn ($block) => HostPricingValidation::dateRangesOverlap(
                (string) $data['blocked_from'],
                (string) $data['blocked_to'],
                (string) $block->blocked_from,
                (string) $block->blocked_to,
            ));

        if ($overlapping) {
            return response()->json([
                'message' => sprintf(
                    'This block overlaps an existing block (%s – %s). Remove or adjust the existing block first.',
                    $overlapping->blocked_from->format('j M Y'),
                    $overlapping->blocked_to->format('j M Y'),
                ),
            ], 422);
        }

        $block = $guestHouse->availabilityBlocks()->create([
            ...$data,
            'reason' => 'owner_use',
            'source' => 'manual',
        ]);

        return response()->json(['data' => $block], 201);
    }

    public function destroyAvailabilityBlock(GuestHouse $guestHouse, GuestHouseAvailabilityBlock $block): JsonResponse
    {
        $this->authorize('update', $guestHouse);
        abort_unless($block->guest_house_id === $guestHouse->id, 404);
        $block->delete();

        return response()->json(['message' => 'Block removed.']);
    }

    private function validatedData(Request $request, ?GuestHouse $guestHouse = null): array
    {
        $data = $request->validate([
            'name' => [$guestHouse ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('guest_houses', 'slug')->ignore($guestHouse?->id)],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'type' => ['nullable', Rule::enum(GuestHouseType::class)],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'max_guests' => ['nullable', 'integer', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'beds' => ['nullable', 'integer', 'min:0'],
            'min_nights' => ['nullable', 'integer', 'min:0'],
            'max_nights' => ['nullable', 'integer', 'min:0'],
            'base_price_per_night_euros' => ['nullable', 'numeric', 'min:0'],
            'cleaning_fee_euros' => ['nullable', 'numeric', 'min:0'],
            'security_deposit_euros' => ['nullable', 'numeric', 'min:0'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'cancellation_policy' => ['nullable', Rule::enum(GuestHouseCancellationPolicy::class)],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:guest_house_amenities,id'],
            'seasonal_prices' => ['nullable', 'array'],
            'seasonal_prices.*.id' => ['nullable', 'integer'],
            'seasonal_prices.*.name' => ['required', 'string', 'max:255'],
            'seasonal_prices.*.date_from' => ['required', 'date'],
            'seasonal_prices.*.date_to' => ['required', 'date'],
            'seasonal_prices.*.price_per_night_euros' => ['nullable', 'numeric', 'min:0'],
            'seasonal_prices.*.minimum_nights' => ['nullable', 'integer', 'min:1'],
            'room_details' => ['nullable', 'array'],
            'room_details.*.id' => ['nullable', 'integer'],
            'room_details.*.title' => ['required', 'string', 'max:255'],
            'room_details.*.text' => ['nullable', 'string', 'max:2000'],
            'room_details.*.dim' => ['nullable', 'string', 'max:255'],
        ]);

        if (array_key_exists('base_price_per_night_euros', $data)) {
            $data['base_price_per_night'] = $data['base_price_per_night_euros'] === null
                ? 0
                : (int) round($data['base_price_per_night_euros'] * 100);
            unset($data['base_price_per_night_euros']);
        }
        if (isset($data['cleaning_fee_euros'])) {
            $data['cleaning_fee'] = (int) round($data['cleaning_fee_euros'] * 100);
            unset($data['cleaning_fee_euros']);
        }
        if (isset($data['security_deposit_euros'])) {
            $data['security_deposit'] = (int) round($data['security_deposit_euros'] * 100);
            unset($data['security_deposit_euros']);
        }

        return $data;
    }

    private function syncSeasonalPrices(GuestHouse $guestHouse, array $rows): void
    {
        $ids = [];
        foreach ($rows as $row) {
            $priceCents = isset($row['price_per_night_euros'])
                ? (int) round($row['price_per_night_euros'] * 100)
                : (int) ($row['price_per_night'] ?? 0);

            $payload = [
                'name' => $row['name'],
                'date_from' => $row['date_from'],
                'date_to' => $row['date_to'],
                'price_per_night' => $priceCents,
                'minimum_nights' => isset($row['minimum_nights']) && $row['minimum_nights'] !== '' && $row['minimum_nights'] !== null
                    ? (int) $row['minimum_nights']
                    : null,
            ];

            if (! empty($row['id'])) {
                $sp = GuestHouseSeasonalPrice::query()
                    ->where('guest_house_id', $guestHouse->id)
                    ->whereKey($row['id'])
                    ->first();
                if ($sp) {
                    $sp->update($payload);
                    $ids[] = $sp->id;
                    continue;
                }
            }

            $created = $guestHouse->seasonalPrices()->create($payload);
            $ids[] = $created->id;
        }

        $guestHouse->seasonalPrices()->whereNotIn('id', $ids)->delete();
    }

    private function hostRelations(): array
    {
        $relations = ['amenities', 'images', 'seasonalPrices'];

        if (Schema::hasTable('guest_house_room_details')) {
            $relations[] = 'roomDetails';
        }

        return $relations;
    }

    private function loadHostRelations(GuestHouse $guestHouse): void
    {
        $guestHouse->load($this->hostRelations());
    }

    private function loadHostMediaRelations(GuestHouse $guestHouse): void
    {
        $relations = ['images'];

        if (Schema::hasTable('guest_house_room_details')) {
            $relations[] = 'roomDetails';
        }

        $guestHouse->load($relations);
    }

    private function syncRoomDetails(GuestHouse $guestHouse, array $rows): void
    {
        if (! Schema::hasTable('guest_house_room_details')) {
            return;
        }

        $ids = [];

        foreach ($rows as $index => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $payload = [
                'title' => $title,
                'text' => filled($row['text'] ?? null) ? trim((string) $row['text']) : null,
                'dim' => filled($row['dim'] ?? null) ? trim((string) $row['dim']) : null,
                'sort_order' => $index,
            ];

            if (! empty($row['id'])) {
                $detail = GuestHouseRoomDetail::query()
                    ->where('guest_house_id', $guestHouse->id)
                    ->whereKey($row['id'])
                    ->first();

                if ($detail) {
                    $detail->update($payload);
                    $ids[] = $detail->id;

                    continue;
                }
            }

            $created = $guestHouse->roomDetails()->create($payload);
            $ids[] = $created->id;
        }

        $guestHouse->roomDetails()
            ->whereNotIn('id', $ids)
            ->get()
            ->each(function (GuestHouseRoomDetail $detail): void {
                if ($detail->image_path) {
                    Storage::disk('public')->delete($detail->image_path);
                }
                $detail->delete();
            });
    }
}
