<?php

namespace App\Http\Controllers\Api\Host;

use App\Enums\ListingApprovalStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Host\HostCarResource;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\ExtraHourFare;
use App\Models\HourlyFare;
use App\Models\SpecialPrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HostCarController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Car::class);

        $cars = Car::query()
            ->where('user_id', $request->user()->id)
            ->with('subCategory.mainCategory')
            ->withCount('carUnits')
            ->orderByDesc('updated_at')
            ->paginate((int) $request->query('per_page', 20));

        return response()->json([
            'data' => HostCarResource::collection($cars),
            'meta' => [
                'current_page' => $cars->currentPage(),
                'last_page' => $cars->lastPage(),
                'total' => $cars->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Car::class);

        $data = $this->validatedData($request);
        $car = Car::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'is_active' => false,
            'listing_status' => ListingApprovalStatus::Draft,
        ]);

        $this->syncRelations($request, $car);
        $car->load(['subCategory.mainCategory', 'locations', 'characteristics', 'rentalOptions']);

        return response()->json(['data' => new HostCarResource($car)], 201);
    }

    public function show(Car $car): JsonResponse
    {
        $this->authorize('view', $car);
        $car->load(['subCategory.mainCategory', 'locations', 'characteristics', 'rentalOptions']);
        $car->loadCount('carUnits');

        return response()->json(['data' => new HostCarResource($car)]);
    }

    public function update(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $this->validatedData($request, $car);
        unset($data['is_active'], $data['listing_status']);
        $car->update($data);
        $this->syncRelations($request, $car);

        $car->load(['subCategory.mainCategory', 'locations', 'characteristics', 'rentalOptions']);
        $car->loadCount('carUnits');

        return response()->json(['data' => new HostCarResource($car)]);
    }

    public function destroy(Car $car): JsonResponse
    {
        $this->authorize('delete', $car);
        $car->delete();

        return response()->json(['message' => 'Car deleted.']);
    }

    public function submit(Car $car): JsonResponse
    {
        $this->authorize('submit', $car);

        if (! in_array($car->listing_status, [ListingApprovalStatus::Draft, ListingApprovalStatus::Rejected], true)) {
            return response()->json(['message' => 'Only draft or rejected listings can be submitted.'], 422);
        }

        $car->update([
            'listing_status' => ListingApprovalStatus::PendingReview,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        return response()->json(['data' => new HostCarResource($car->fresh(['subCategory.mainCategory', 'locations', 'characteristics', 'rentalOptions']))]);
    }

    public function uploadImages(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $request->validate([
            'main_image' => ['nullable', 'image', 'max:8192'],
            'og_image' => ['nullable', 'image', 'max:8192'],
            'details_images' => ['nullable', 'array'],
            'details_images.*' => ['image', 'max:8192'],
            'details_image_paths' => ['nullable', 'array'],
            'details_image_paths.*' => ['string'],
        ]);

        if ($request->hasFile('main_image')) {
            $path = $request->file('main_image')->store('cars', 'public');
            $car->update(['main_image_path' => $path]);
        }

        if ($request->hasFile('og_image')) {
            $path = $request->file('og_image')->store('cars/og', 'public');
            $car->update(['og_image' => $path]);
        }

        if ($request->hasFile('details_images')) {
            $paths = $car->details_image_paths ?? [];
            foreach ($request->file('details_images') as $file) {
                $paths[] = $file->store('cars/details', 'public');
            }
            $car->update(['details_image_paths' => $paths]);
        }

        if ($request->has('details_image_paths')) {
            $paths = array_values(array_filter(
                $request->input('details_image_paths', []),
                fn ($path) => filled($path),
            ));
            $car->update(['details_image_paths' => $paths]);
        }

        return response()->json(['data' => new HostCarResource($car->fresh())]);
    }

    public function syncRelationsEndpoint(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);
        $this->syncRelations($request, $car);
        $car->load(['locations', 'characteristics', 'rentalOptions']);

        return response()->json(['data' => new HostCarResource($car)]);
    }

    public function units(Car $car): JsonResponse
    {
        $this->authorize('view', $car);

        return response()->json(['data' => $car->carUnits()->orderBy('sort_order')->get()]);
    }

    public function storeUnit(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $request->validate([
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $unit = $car->carUnits()->create([
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? ($car->carUnits()->max('sort_order') + 1),
        ]);

        $car->update(['units_available' => $car->carUnits()->where('is_active', true)->count()]);

        return response()->json(['data' => $unit], 201);
    }

    public function updateUnit(Request $request, Car $car, int $unitId): JsonResponse
    {
        $this->authorize('update', $car);
        $unit = $car->carUnits()->findOrFail($unitId);

        $data = $request->validate([
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        $unit->update($data);
        $car->update(['units_available' => $car->carUnits()->where('is_active', true)->count()]);

        return response()->json(['data' => $unit]);
    }

    public function destroyUnit(Car $car, int $unitId): JsonResponse
    {
        $this->authorize('update', $car);
        $unit = $car->carUnits()->findOrFail($unitId);
        $unit->delete();
        $car->update(['units_available' => max(1, $car->carUnits()->where('is_active', true)->count())]);

        return response()->json(['message' => 'Unit deleted.']);
    }

    public function dailyFares(Car $car): JsonResponse
    {
        $this->authorize('view', $car);

        return response()->json(['data' => $car->dailyFares()->with('priceType')->get()]);
    }

    public function storeDailyFare(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $request->validate([
            'price_type_id' => ['required', 'exists:price_types,id'],
            'from_days' => ['required', 'integer', 'min:1'],
            'to_days' => ['required', 'integer', 'gte:from_days'],
            'price_per_day_cents' => ['required_without:price_per_day_euros', 'integer', 'min:0'],
            'price_per_day_euros' => ['required_without:price_per_day_cents', 'numeric', 'min:0'],
        ]);

        if (isset($data['price_per_day_euros'])) {
            $data['price_per_day_cents'] = (int) round($data['price_per_day_euros'] * 100);
        }

        $fare = $car->dailyFares()->create($data);

        return response()->json(['data' => $fare->load('priceType')], 201);
    }

    public function updateDailyFare(Request $request, Car $car, DailyFare $dailyFare): JsonResponse
    {
        $this->authorize('update', $car);
        abort_unless($dailyFare->car_id === $car->id, 404);

        $data = $request->validate([
            'price_type_id' => ['sometimes', 'exists:price_types,id'],
            'from_days' => ['sometimes', 'integer', 'min:1'],
            'to_days' => ['sometimes', 'integer'],
            'price_per_day_cents' => ['sometimes', 'integer', 'min:0'],
            'price_per_day_euros' => ['sometimes', 'numeric', 'min:0'],
        ]);

        if (isset($data['price_per_day_euros'])) {
            $data['price_per_day_cents'] = (int) round($data['price_per_day_euros'] * 100);
        }

        $dailyFare->update($data);

        return response()->json(['data' => $dailyFare->fresh('priceType')]);
    }

    public function destroyDailyFare(Car $car, DailyFare $dailyFare): JsonResponse
    {
        $this->authorize('update', $car);
        abort_unless($dailyFare->car_id === $car->id, 404);
        $dailyFare->delete();

        return response()->json(['message' => 'Fare deleted.']);
    }

    public function hourlyFares(Car $car): JsonResponse
    {
        $this->authorize('view', $car);

        return response()->json(['data' => $car->hourlyFares()->with('priceType')->get()]);
    }

    public function storeHourlyFare(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $request->validate([
            'price_type_id' => ['required', 'exists:price_types,id'],
            'min_minutes' => ['required', 'integer', 'min:1'],
            'max_minutes' => ['required', 'integer', 'gte:min_minutes'],
            'total_price_cents' => ['required_without:total_price_euros', 'integer', 'min:0'],
            'total_price_euros' => ['required_without:total_price_cents', 'numeric', 'min:0'],
        ]);

        if (isset($data['total_price_euros'])) {
            $data['total_price_cents'] = (int) round($data['total_price_euros'] * 100);
            unset($data['total_price_euros']);
        }

        $fare = $car->hourlyFares()->create($data);

        return response()->json(['data' => $fare->load('priceType')], 201);
    }

    public function destroyHourlyFare(Car $car, HourlyFare $hourlyFare): JsonResponse
    {
        $this->authorize('update', $car);
        abort_unless($hourlyFare->car_id === $car->id, 404);
        $hourlyFare->delete();

        return response()->json(['message' => 'Fare deleted.']);
    }

    public function extraHourFares(Car $car): JsonResponse
    {
        $this->authorize('view', $car);

        return response()->json(['data' => $car->extraHourFares()->with('priceType')->get()]);
    }

    public function storeExtraHourFare(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $request->validate([
            'price_type_id' => ['required', 'exists:price_types,id'],
            'charge_per_extra_hour_cents' => ['required_without:charge_per_extra_hour_euros', 'integer', 'min:0'],
            'charge_per_extra_hour_euros' => ['required_without:charge_per_extra_hour_cents', 'numeric', 'min:0'],
        ]);

        if (isset($data['charge_per_extra_hour_euros'])) {
            $data['charge_per_extra_hour_cents'] = (int) round($data['charge_per_extra_hour_euros'] * 100);
            unset($data['charge_per_extra_hour_euros']);
        }

        $fare = $car->extraHourFares()->create($data);

        return response()->json(['data' => $fare->load('priceType')], 201);
    }

    public function destroyExtraHourFare(Car $car, ExtraHourFare $extraHourFare): JsonResponse
    {
        $this->authorize('update', $car);
        abort_unless($extraHourFare->car_id === $car->id, 404);
        $extraHourFare->delete();

        return response()->json(['message' => 'Fare deleted.']);
    }

    public function availabilityBlocks(Car $car): JsonResponse
    {
        $this->authorize('view', $car);

        return response()->json(['data' => $car->availabilityBlocks()->orderBy('starts_at')->get()]);
    }

    public function storeAvailabilityBlock(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'units_blocked' => ['integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $block = $car->availabilityBlocks()->create([
            ...$data,
            'source' => 'manual',
            'is_active' => true,
            'units_blocked' => $data['units_blocked'] ?? 1,
        ]);

        return response()->json(['data' => $block], 201);
    }

    public function destroyAvailabilityBlock(Car $car, AvailabilityBlock $block): JsonResponse
    {
        $this->authorize('update', $car);
        abort_unless($block->car_id === $car->id, 404);
        $block->delete();

        return response()->json(['message' => 'Block removed.']);
    }

    public function specialPrices(Car $car): JsonResponse
    {
        $this->authorize('view', $car);

        $prices = SpecialPrice::query()
            ->whereJsonContains('vehicle_ids', $car->id)
            ->orderByDesc('date_from')
            ->get();

        return response()->json(['data' => $prices]);
    }

    public function storeSpecialPrice(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'type' => ['required', 'string'],
            'value_mode' => ['required', 'string'],
            'value_fixed_cents' => ['nullable', 'integer', 'min:0'],
            'value_percent_bips' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['vehicle_ids'] = [$car->id];
        $price = SpecialPrice::query()->create($data);

        return response()->json(['data' => $price], 201);
    }

    public function destroySpecialPrice(Car $car, SpecialPrice $specialPrice): JsonResponse
    {
        $this->authorize('update', $car);
        abort_unless(in_array($car->id, $specialPrice->vehicle_ids ?? [], true), 404);
        $specialPrice->delete();

        return response()->json(['message' => 'Special price deleted.']);
    }

    private function validatedData(Request $request, ?Car $car = null): array
    {
        $data = $request->validate([
            'sub_category_id' => ['sometimes', 'exists:sub_categories,id'],
            'category_id' => ['sometimes', 'exists:sub_categories,id'],
            'name' => [$car ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('cars', 'slug')->ignore($car?->id)],
            'description' => ['nullable', 'string'],
            'transmission' => ['nullable', 'string', 'max:50'],
            'fuel_type' => ['nullable', 'string', 'max:50'],
            'units_available' => ['nullable', 'integer', 'min:1'],
            'ical_import_url' => ['nullable', 'url', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
            'pickup_location_ids' => ['nullable', 'array'],
            'pickup_location_ids.*' => ['integer', 'exists:locations,id'],
            'dropoff_location_ids' => ['nullable', 'array'],
            'dropoff_location_ids.*' => ['integer', 'exists:locations,id'],
            'characteristic_ids' => ['nullable', 'array'],
            'characteristic_ids.*' => ['integer', 'exists:characteristics,id'],
            'rental_option_ids' => ['nullable', 'array'],
            'rental_option_ids.*' => ['integer', 'exists:rental_options,id'],
        ]);

        if (! isset($data['sub_category_id']) && isset($data['category_id'])) {
            $data['sub_category_id'] = $data['category_id'];
        }

        unset($data['category_id']);

        if (! $car && ! isset($data['sub_category_id'])) {
            abort(422, 'The sub category id field is required.');
        }

        return $data;
    }

    private function syncRelations(Request $request, Car $car): void
    {
        if ($request->has('pickup_location_ids') || $request->has('dropoff_location_ids')) {
            $pickupIds = array_map('intval', $request->input('pickup_location_ids', []));
            $dropoffIds = array_map('intval', $request->input('dropoff_location_ids', []));

            $sync = [];
            foreach (array_unique(array_merge($pickupIds, $dropoffIds)) as $locationId) {
                $sync[$locationId] = [
                    'allows_pickup' => in_array($locationId, $pickupIds, true),
                    'allows_dropoff' => in_array($locationId, $dropoffIds, true),
                ];
            }
            $car->locations()->sync($sync);
        } elseif ($request->has('location_ids')) {
            $sync = [];
            foreach ($request->input('location_ids', []) as $locationId) {
                $sync[$locationId] = ['allows_pickup' => true, 'allows_dropoff' => true];
            }
            $car->locations()->sync($sync);
        }

        if ($request->has('characteristic_ids')) {
            $car->characteristics()->sync($request->input('characteristic_ids', []));
        }

        if ($request->has('rental_option_ids')) {
            $car->rentalOptions()->sync($request->input('rental_option_ids', []));
        }
    }
}
