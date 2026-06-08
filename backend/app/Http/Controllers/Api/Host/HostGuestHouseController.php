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
use App\Models\GuestHouseSeasonalPrice;
use App\Services\Email\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', GuestHouse::class);

        $data = $this->validatedData($request);
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
            ...$data,
            'user_id' => $request->user()->id,
            'status' => GuestHouseStatus::Draft,
        ]);

        if ($request->has('amenity_ids')) {
            $house->amenities()->sync($request->input('amenity_ids', []));
        }

        $house->load(['amenities', 'images', 'seasonalPrices']);

        return response()->json(['data' => new HostGuestHouseResource($house)], 201);
    }

    public function show(GuestHouse $guestHouse): JsonResponse
    {
        $this->authorize('view', $guestHouse);

        $guestHouse->load(['amenities', 'images', 'seasonalPrices']);

        return response()->json(['data' => new HostGuestHouseResource($guestHouse)]);
    }

    public function update(Request $request, GuestHouse $guestHouse): JsonResponse
    {
        $this->authorize('update', $guestHouse);

        $data = $this->validatedData($request, $guestHouse);
        unset($data['status']);
        $guestHouse->update($data);

        if ($request->has('amenity_ids')) {
            $guestHouse->amenities()->sync($request->input('amenity_ids', []));
        }

        if ($request->has('seasonal_prices')) {
            $this->syncSeasonalPrices($guestHouse, $request->input('seasonal_prices', []));
        }

        $guestHouse->load(['amenities', 'images', 'seasonalPrices']);

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

        return response()->json(['data' => new HostGuestHouseResource($guestHouse->fresh(['amenities', 'images', 'seasonalPrices']))]);
    }

    public function uploadImages(Request $request, GuestHouse $guestHouse): JsonResponse
    {
        $this->authorize('update', $guestHouse);

        $request->validate([
            'thumbnail' => ['nullable', 'image', 'max:8192'],
            'og_image' => ['nullable', 'image', 'max:8192'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:8192'],
            'gallery_order' => ['nullable', 'array'],
            'gallery_order.*' => ['integer'],
        ]);

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('guesthouses/thumbnails', 'public');
            $guestHouse->update(['thumbnail' => $path]);
        }

        if ($request->hasFile('og_image')) {
            $path = $request->file('og_image')->store('guesthouses/og', 'public');
            $guestHouse->update(['og_image' => $path]);
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

        $guestHouse->load('images');

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
        ]);

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
            'max_guests' => ['nullable', 'integer', 'min:1'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:1'],
            'beds' => ['nullable', 'integer', 'min:1'],
            'min_nights' => ['nullable', 'integer', 'min:1'],
            'max_nights' => ['nullable', 'integer', 'min:1'],
            'base_price_per_night_euros' => ['nullable', 'numeric', 'min:0'],
            'cleaning_fee_euros' => ['nullable', 'numeric', 'min:0'],
            'security_deposit_euros' => ['nullable', 'numeric', 'min:0'],
            'check_in_time' => ['nullable', 'date_format:H:i'],
            'check_out_time' => ['nullable', 'date_format:H:i'],
            'cancellation_policy' => ['nullable', Rule::enum(GuestHouseCancellationPolicy::class)],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'amenity_ids' => ['nullable', 'array'],
            'amenity_ids.*' => ['integer', 'exists:guest_house_amenities,id'],
            'seasonal_prices' => ['nullable', 'array'],
        ]);

        if (isset($data['base_price_per_night_euros'])) {
            $data['base_price_per_night'] = (int) round($data['base_price_per_night_euros'] * 100);
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
                'minimum_nights' => $row['minimum_nights'] ?? null,
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
}
