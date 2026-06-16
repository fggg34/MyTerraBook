<?php

namespace App\Http\Controllers\Api\Host;

use App\Enums\ListingApprovalStatus;
use App\Enums\DriveType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Host\HostCarResource;
use App\Http\Resources\Host\HostLocationFeeResource;
use App\Http\Resources\Host\HostOutOfHoursFeeResource;
use App\Models\AvailabilityBlock;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\ExtraHourFare;
use App\Models\HourlyFare;
use App\Models\LocationFee;
use App\Models\OutOfHoursFee;
use App\Models\SpecialPrice;
use App\Services\Email\EmailService;
use App\Services\ListingSeoService;
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

    public function store(Request $request, ListingSeoService $seo): JsonResponse
    {
        $this->authorize('create', Car::class);

        $data = $this->validatedData($request);
        // units_available is always derived from actual CarUnit rows, never set
        // directly from the form, so the counter and the fleet cannot disagree.
        unset($data['units_available']);
        $car = Car::query()->create([
            ...$data,
            'user_id' => $request->user()->id,
            'is_active' => false,
            'listing_status' => ListingApprovalStatus::Draft,
            'units_available' => 1,
        ]);

        $this->ensureAtLeastOneUnit($car);
        $this->syncRelations($request, $car);
        $seo->syncCar($car);
        $car->load(['subCategory.mainCategory', 'locations', 'characteristics', 'rentalOptions', 'rentalConditions']);

        return response()->json(['data' => new HostCarResource($car)], 201);
    }

    public function show(Car $car): JsonResponse
    {
        $this->authorize('view', $car);
        $this->loadCarRelations($car);

        return response()->json(['data' => new HostCarResource($car)]);
    }

    public function update(Request $request, Car $car, ListingSeoService $seo): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $this->validatedData($request, $car);
        unset($data['is_active'], $data['listing_status'], $data['units_available']);
        $car->update($data);
        $this->syncRelations($request, $car);
        $seo->syncCar($car);
        $this->loadCarRelations($car->fresh());

        return response()->json(['data' => new HostCarResource($car)]);
    }

    public function destroy(Car $car): JsonResponse
    {
        $this->authorize('delete', $car);
        $car->delete();

        return response()->json(['message' => 'Car deleted.']);
    }

    public function submit(Car $car, EmailService $email): JsonResponse
    {
        $this->authorize('submit', $car);

        if (! in_array($car->listing_status, [ListingApprovalStatus::Draft, ListingApprovalStatus::Rejected], true)) {
            return response()->json(['message' => 'Only draft or rejected listings can be submitted.'], 422);
        }

        $this->ensureAtLeastOneUnit($car);

        if ($submitError = $this->submitReadinessError($car)) {
            return response()->json(['message' => $submitError], 422);
        }

        $car->update([
            'listing_status' => ListingApprovalStatus::PendingReview,
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        $car->loadMissing('host');
        if ($hostEmail = $car->host?->email) {
            $email->send('listing_submitted', $hostEmail, [
                'host_name' => $car->host?->name,
                'listing_name' => $car->name,
            ]);
        }

        return response()->json(['data' => new HostCarResource($car->fresh(['subCategory.mainCategory', 'locations', 'characteristics', 'rentalOptions', 'rentalConditions']))]);
    }

    public function uploadImages(Request $request, Car $car, ListingSeoService $seo): JsonResponse
    {
        $this->authorize('update', $car);

        $request->validate([
            'main_image' => ['nullable', 'image', 'max:8192'],
            'details_images' => ['nullable', 'array'],
            'details_images.*' => ['image', 'max:8192'],
            'details_image_paths' => ['nullable', 'array'],
            'details_image_paths.*' => ['string'],
        ]);

        if ($request->hasFile('main_image')) {
            $path = $request->file('main_image')->store('cars', 'public');
            $car->update(['main_image_path' => $path]);
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

        if ($request->hasFile('main_image')) {
            $seo->syncCar($car);
        }

        return response()->json(['data' => new HostCarResource($car->fresh())]);
    }

    public function syncRelationsEndpoint(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);
        $this->syncRelations($request, $car);
        $car->load(['locations', 'characteristics', 'rentalOptions', 'rentalConditions']);

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

    public function locationFees(Car $car): JsonResponse
    {
        $this->authorize('view', $car);

        $fees = $car->locationFees()
            ->with(['pickupLocation', 'dropoffLocation'])
            ->orderBy('id')
            ->get();

        return response()->json(['data' => HostLocationFeeResource::collection($fees)]);
    }

    public function storeLocationFee(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $request->validate([
            'pickup_location_id' => ['required', 'integer', 'exists:locations,id'],
            'dropoff_location_id' => ['required', 'integer', 'exists:locations,id'],
            'cost_cents' => ['required_without:cost_euros', 'integer', 'min:0'],
            'cost_euros' => ['required_without:cost_cents', 'numeric', 'min:0'],
            'multiply_by_days' => ['boolean'],
            'is_one_way_fee' => ['boolean'],
        ]);

        $this->assertLocationsLinkedToCar($car, [
            (int) $data['pickup_location_id'],
            (int) $data['dropoff_location_id'],
        ]);

        if (isset($data['cost_euros'])) {
            $data['cost_cents'] = (int) round($data['cost_euros'] * 100);
        }

        $fee = $car->locationFees()->create([
            'pickup_location_id' => $data['pickup_location_id'],
            'dropoff_location_id' => $data['dropoff_location_id'],
            'cost_cents' => $data['cost_cents'],
            'multiply_by_days' => $data['multiply_by_days'] ?? false,
            'is_one_way_fee' => $data['is_one_way_fee'] ?? false,
            'is_active' => true,
        ]);

        $fee->load(['pickupLocation', 'dropoffLocation']);

        return response()->json(['data' => new HostLocationFeeResource($fee)], 201);
    }

    public function destroyLocationFee(Car $car, LocationFee $locationFee): JsonResponse
    {
        $this->authorize('update', $car);
        abort_unless($locationFee->car_id === $car->id, 404);
        $locationFee->delete();

        return response()->json(['message' => 'Location fee deleted.']);
    }

    public function outOfHoursFees(Car $car): JsonResponse
    {
        $this->authorize('view', $car);

        $fees = $this->outOfHoursFeesForCar($car);

        return response()->json(['data' => HostOutOfHoursFeeResource::collection($fees)]);
    }

    public function storeOutOfHoursFee(Request $request, Car $car): JsonResponse
    {
        $this->authorize('update', $car);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'time_from' => ['required', 'date_format:H:i'],
            'time_to' => ['required', 'date_format:H:i'],
            'applies_to' => ['required', Rule::in(['pickup', 'dropoff', 'both'])],
            'pickup_cost_cents' => ['nullable', 'integer', 'min:0'],
            'dropoff_cost_cents' => ['nullable', 'integer', 'min:0'],
            'pickup_cost_euros' => ['nullable', 'numeric', 'min:0'],
            'dropoff_cost_euros' => ['nullable', 'numeric', 'min:0'],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'exists:locations,id'],
        ]);

        if (isset($data['pickup_cost_euros'])) {
            $data['pickup_cost_cents'] = (int) round($data['pickup_cost_euros'] * 100);
        }
        if (isset($data['dropoff_cost_euros'])) {
            $data['dropoff_cost_cents'] = (int) round($data['dropoff_cost_euros'] * 100);
        }

        $locationIds = array_map('intval', $data['location_ids'] ?? []);
        if ($locationIds !== []) {
            $this->assertLocationsLinkedToCar($car, $locationIds);
        }

        $pickupCost = (int) ($data['pickup_cost_cents'] ?? 0);
        $dropoffCost = (int) ($data['dropoff_cost_cents'] ?? 0);

        $fee = OutOfHoursFee::query()->create([
            'name' => $data['name'] ?? 'Out-of-hours',
            'time_from' => $data['time_from'],
            'time_to' => $data['time_to'],
            'applies_to' => $data['applies_to'],
            'cost_cents' => max($pickupCost, $dropoffCost),
            'pickup_cost_cents' => $pickupCost,
            'dropoff_cost_cents' => $dropoffCost,
            'vehicle_ids' => [$car->id],
            'location_ids' => $locationIds === [] ? null : $locationIds,
            'is_active' => true,
        ]);

        return response()->json(['data' => new HostOutOfHoursFeeResource($fee)], 201);
    }

    public function destroyOutOfHoursFee(Car $car, OutOfHoursFee $outOfHoursFee): JsonResponse
    {
        $this->authorize('update', $car);
        abort_unless(in_array($car->id, $outOfHoursFee->vehicle_ids ?? [], true), 404);
        $outOfHoursFee->delete();

        return response()->json(['message' => 'Out-of-hours fee deleted.']);
    }

    private function validatedData(Request $request, ?Car $car = null): array
    {
        $data = $request->validate([
            'sub_category_id' => ['sometimes', 'exists:sub_categories,id'],
            'category_id' => ['sometimes', 'exists:sub_categories,id'],
            'name' => [$car ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('cars', 'slug')->ignore($car?->id)],
            'description' => ['nullable', 'string'],
            'transmission' => ['nullable', 'string', Rule::in(['manual', 'automatic'])],
            'fuel_type' => ['nullable', 'string', 'max:50'],
            'drive_type' => ['nullable', 'string', Rule::in(DriveType::values())],
            'seats' => ['nullable', 'integer', 'min:0', 'max:50'],
            'sleeps' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bags' => ['nullable', 'integer', 'min:0', 'max:50'],
            'year' => ['nullable', 'integer', 'min:1990', 'max:2026'],
            'units_available' => ['nullable', 'integer', 'min:1'],
            'ical_import_url' => ['nullable', 'url', 'max:500'],
            'pickup_time_from' => ['nullable', 'date_format:H:i'],
            'pickup_time_to' => ['nullable', 'date_format:H:i', 'after:pickup_time_from'],
            'dropoff_time_from' => ['nullable', 'date_format:H:i'],
            'dropoff_time_to' => ['nullable', 'date_format:H:i', 'after:dropoff_time_from'],
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
            'rental_condition_ids' => ['nullable', 'array'],
            'rental_condition_ids.*' => ['integer', 'exists:rental_conditions,id'],
        ]);

        if (! isset($data['sub_category_id']) && isset($data['category_id'])) {
            $data['sub_category_id'] = $data['category_id'];
        }

        unset($data['category_id']);

        if (! $car && ! isset($data['sub_category_id'])) {
            abort(422, 'The sub category id field is required.');
        }

        if (isset($data['sub_category_id'])) {
            $mainSlug = \App\Models\SubCategory::query()
                ->with('mainCategory')
                ->find($data['sub_category_id'])
                ?->mainCategory
                ?->slug;
        } elseif ($car) {
            $car->loadMissing('subCategory.mainCategory');
            $mainSlug = $car->subCategory?->mainCategory?->slug;
        } else {
            $mainSlug = null;
        }

        if ($mainSlug && $mainSlug !== 'campervan') {
            $data['sleeps'] = 0;
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

        if ($request->has('rental_condition_ids')) {
            $car->rentalConditions()->sync($request->input('rental_condition_ids', []));
        }
    }

    private function loadCarRelations(Car $car): void
    {
        $car->load([
            'subCategory.mainCategory',
            'locations',
            'characteristics',
            'rentalOptions',
            'rentalConditions',
            'locationFees.pickupLocation',
            'locationFees.dropoffLocation',
        ]);
        $car->loadCount('carUnits');
        $car->setRelation('outOfHoursFees', $this->outOfHoursFeesForCar($car));
    }

    private function outOfHoursFeesForCar(Car $car)
    {
        return OutOfHoursFee::query()
            ->whereJsonContains('vehicle_ids', $car->id)
            ->orderBy('id')
            ->get();
    }

    /**
     * Guarantee a car has at least one real CarUnit and that units_available
     * always reflects the active unit count. Auto-heals legacy cars that were
     * created with a units_available counter but no unit rows.
     */
    private function ensureAtLeastOneUnit(Car $car): void
    {
        if ($car->carUnits()->count() === 0) {
            $desired = max(1, (int) ($car->units_available ?? 1));
            for ($i = 0; $i < $desired; $i++) {
                $car->carUnits()->create(['is_active' => true, 'sort_order' => $i]);
            }
        }

        $car->update([
            'units_available' => max(1, $car->carUnits()->where('is_active', true)->count()),
        ]);
    }

    private function submitReadinessError(Car $car): ?string
    {
        $car->loadMissing(['subCategory.mainCategory', 'locations']);

        if (trim((string) $car->name) === '') {
            return 'A vehicle name is required before submitting for review.';
        }

        if (! $car->sub_category_id) {
            return 'A sub category is required before submitting for review.';
        }

        $hasPickup = $car->locations->contains(fn ($loc) => (bool) $loc->pivot->allows_pickup);
        if (! $hasPickup) {
            return 'At least one pickup location is required before submitting for review.';
        }

        $hasDropoff = $car->locations->contains(fn ($loc) => (bool) $loc->pivot->allows_dropoff);
        if (! $hasDropoff) {
            return 'At least one dropoff location is required before submitting for review.';
        }

        if ($car->carUnits()->where('is_active', true)->count() < 1) {
            return 'At least one available unit is required before submitting for review.';
        }

        if (! $car->dailyFares()->exists()) {
            return 'At least one daily fare is required before submitting for review.';
        }

        foreach (['pickup_time_from', 'pickup_time_to', 'dropoff_time_from', 'dropoff_time_to'] as $field) {
            if (blank($car->{$field})) {
                return 'Pickup and drop-off times are required before submitting for review.';
            }
        }

        $isCampervan = $car->subCategory?->mainCategory?->slug === 'campervan';

        if (blank($car->transmission)) {
            return 'Transmission is required before submitting for review.';
        }

        if (blank($car->fuel_type)) {
            return 'Fuel type is required before submitting for review.';
        }

        if (blank($car->drive_type)) {
            return 'Drive system is required before submitting for review.';
        }

        if (($car->bags ?? 0) < 1) {
            return 'Bags capacity must be at least 1 before submitting for review.';
        }

        if ($isCampervan) {
            if (($car->sleeps ?? 0) < 1) {
                return 'Sleeps (berths) must be at least 1 before submitting a campervan for review.';
            }
        } elseif (($car->seats ?? 0) < 1) {
            return 'Seats must be at least 1 before submitting for review.';
        }

        return null;
    }

    /**
     * @param  list<int>  $locationIds
     */
    private function assertLocationsLinkedToCar(Car $car, array $locationIds): void
    {
        $car->loadMissing('locations');
        $linked = $car->locations->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach (array_unique($locationIds) as $locationId) {
            abort_unless(in_array($locationId, $linked, true), 422, 'Location must be assigned to this vehicle first.');
        }
    }
}
