<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CarDetailResource;
use App\Http\Resources\Api\CarListResource;
use App\Http\Resources\Api\LocationResource;
use App\Http\Resources\Api\MainCategoryResource;
use App\Http\Resources\Api\SubCategoryResource;
use App\Models\BookingRestriction;
use App\Models\Car;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\LocationFee;
use App\Models\MainCategory;
use App\Models\Order;
use App\Models\PriceType;
use App\Models\SubCategory;
use App\Services\OrderAvailabilityService;
use App\Services\RentalQuoteService;
use App\Support\DailyFarePricing;
use App\Support\Money;
use App\Support\QuotePresentation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CatalogController extends Controller
{
    public function __construct(
        private readonly OrderAvailabilityService $availabilityService,
        private readonly RentalQuoteService $quoteService,
    ) {}

    public function mainCategories(): JsonResponse
    {
        $rows = MainCategory::query()->where('is_active', true)->orderBy('sort_order')->get();

        return response()->json(['data' => MainCategoryResource::collection($rows)]);
    }

    public function subCategories(Request $request): JsonResponse
    {
        $query = SubCategory::query()
            ->with('mainCategory')
            ->where('is_active', true)
            ->whereHas('mainCategory', fn ($builder) => $builder->where('is_active', true))
            ->orderBy('sort_order');

        if ($request->filled('main_category')) {
            $query->whereHas('mainCategory', fn ($builder) => $builder->where('slug', $request->query('main_category')));
        }

        if ($request->boolean('search_filters_only')) {
            $query->where('is_search_filter', true);
        }

        return response()->json(['data' => SubCategoryResource::collection($query->get())]);
    }

    /** @deprecated Use subCategories(), kept for storefront filter compatibility. */
    public function categories(Request $request): JsonResponse
    {
        return $this->subCategories($request);
    }

    public function locations(): JsonResponse
    {
        $rows = Location::query()->where('is_active', true)->orderBy('name')->get();

        return response()->json(['data' => LocationResource::collection($rows)]);
    }

    public function bookingRestrictions(Request $request): JsonResponse
    {
        $pickupDate = $request->query('pickup_date', now()->toDateString());
        $dropoffDate = $request->query('dropoff_date', $pickupDate);

        $restrictions = BookingRestriction::query()
            ->where('is_active', true)
            ->where('date_from', '<=', $dropoffDate)
            ->where('date_to', '>=', $pickupDate)
            ->get();

        $minRentalDays = 1;
        $maxRentalDays = null;

        foreach ($restrictions as $restriction) {
            if ($restriction->min_rental_days !== null && $restriction->min_rental_days > $minRentalDays) {
                $minRentalDays = $restriction->min_rental_days;
            }
            if ($restriction->max_rental_days !== null) {
                $maxRentalDays = $maxRentalDays === null
                    ? $restriction->max_rental_days
                    : min($maxRentalDays, $restriction->max_rental_days);
            }
        }

        return response()->json([
            'min_rental_days' => $minRentalDays,
            'max_rental_days' => $maxRentalDays,
            'restrictions' => $restrictions->map(fn ($restriction) => [
                'id' => $restriction->id,
                'name' => $restriction->name,
                'date_from' => $restriction->date_from->toDateString(),
                'date_to' => $restriction->date_to->toDateString(),
                'min_rental_days' => $restriction->min_rental_days,
                'max_rental_days' => $restriction->max_rental_days,
                'closed_to_arrival' => $restriction->cta_weekdays ?? [],
                'closed_to_departure' => $restriction->ctd_weekdays ?? [],
                'forced_pickup_weekdays' => $restriction->forced_pickup_weekdays ?? [],
            ])->values(),
        ]);
    }

    public function cars(Request $request): JsonResponse
    {
        $pickupId = $request->query('pickup_location_id');
        $dropoffId = $request->query('dropoff_location_id');
        $mainCategorySlug = $request->query('main_category');

        $query = Car::query()
            ->publiclyVisible()
            ->with(['subCategory.mainCategory']);

        if ($mainCategorySlug) {
            $query->whereHas('subCategory.mainCategory', fn ($builder) => $builder->where('slug', $mainCategorySlug));
        }

        if ($pickupId) {
            $query->whereHas('locations', fn ($q) => $q->where('locations.id', $pickupId)->where('car_location.allows_pickup', true));
        }
        if ($dropoffId) {
            $query->whereHas('locations', fn ($q) => $q->where('locations.id', $dropoffId)->where('car_location.allows_dropoff', true));
        }

        if ($request->filled('sub_category_id')) {
            $query->where('sub_category_id', $request->query('sub_category_id'));
        }

        $minPrices = DailyFarePricing::baseFareListSubquery();

        $cars = $query
            ->leftJoinSub($minPrices, 'min_fares', 'min_fares.car_id', '=', 'cars.id')
            ->select('cars.*')
            ->addSelect('min_fares.min_daily_price_cents')
            ->orderBy('cars.name')
            ->get();

        $rows = $cars->map(function (Car $car) use ($request) {
            $row = [
                'id' => $car->id,
                'name' => $car->name,
                'slug' => $car->slug,
                'sub_category_id' => $car->sub_category_id,
                'sub_category_name' => $car->subCategory?->name,
                'main_category_slug' => $car->subCategory?->mainCategory?->slug,
                'main_category_name' => $car->subCategory?->mainCategory?->name,
                'category_id' => $car->sub_category_id,
                'category_name' => $car->subCategory?->name,
                'transmission' => $car->transmission,
                'fuel_type' => $car->fuel_type,
                'drive_type' => $car->drive_type instanceof \App\Enums\DriveType
                    ? $car->drive_type->value
                    : $car->drive_type,
                'seats' => $car->seats,
                'sleeps' => $car->sleeps,
                'bags' => $car->bags,
                'units_available' => $car->units_available,
                'main_image_path' => $car->main_image_path,
                'min_daily_price_cents' => (int) ($car->min_daily_price_cents ?? 0),
            ];

            $searchPricing = $this->resolveSearchPricing($car, $request);
            if ($searchPricing !== null) {
                $row['search_pricing'] = $searchPricing;
            }

            return $row;
        });

        return response()->json([
            'data' => CarListResource::collection($rows),
        ]);
    }

    public function car(Car $car): JsonResponse
    {
        abort_unless($car->isPubliclyVisible(), 404);

        $car->load([
            'subCategory.mainCategory',
            'characteristics',
            'rentalOptions',
            'rentalConditions' => fn ($q) => $q->where('is_active', true),
            'locations',
            'host',
            'listingReviews' => fn ($q) => $q->approved()->latest()->limit(50),
        ]);

        $fromPriceByType = DailyFarePricing::fromPriceCentsByPriceTypeForCar($car->id);

        $priceTypes = PriceType::query()
            ->whereIn('id', array_keys($fromPriceByType))
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (PriceType $pt) => [
                'id' => $pt->id,
                'name' => $pt->name,
                'slug' => $pt->slug,
                'attribute_label' => $pt->attribute_label,
                'attribute_value_per_day' => $pt->attribute_value_per_day,
                'from_price_per_day_cents' => (int) ($fromPriceByType[$pt->id] ?? 0),
            ])
            ->values()
            ->all();

        $locationFees = LocationFee::query()
            ->where('is_active', true)
            ->when(
                $car->isOwnedByHost(),
                fn ($query) => $query->where('car_id', $car->id),
                fn ($query) => $query->whereNull('car_id'),
            )
            ->get(['pickup_location_id', 'dropoff_location_id', 'cost_cents', 'multiply_by_days', 'is_one_way_fee', 'apply_inverted'])
            ->map(fn (LocationFee $fee) => [
                'pickup_location_id' => $fee->pickup_location_id,
                'dropoff_location_id' => $fee->dropoff_location_id,
                'cost_cents' => (int) $fee->cost_cents,
                'multiply_by_days' => (bool) $fee->multiply_by_days,
                'is_one_way_fee' => (bool) $fee->is_one_way_fee,
                'apply_inverted' => (bool) $fee->apply_inverted,
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => new CarDetailResource($car, $priceTypes, $locationFees),
        ]);
    }

    public function availabilityCalendar(Car $car): JsonResponse
    {
        $orders = Order::query()
            ->where('car_id', $car->id)
            ->where('order_status', OrderStatus::Confirmed)
            ->select(['id', 'pickup_at', 'dropoff_at'])
            ->orderBy('pickup_at')
            ->get();

        $booked = $orders->map(fn (Order $o) => [
            'id' => $o->id,
            'start' => $o->pickup_at->toIso8601String(),
            'end' => $o->dropoff_at->toIso8601String(),
        ]);

        $blocked = $this->availabilityService->blockedWindowsForCar($car->id)
            ->map(fn ($block) => [
                'id' => $block->id,
                'source' => $block->source,
                'start' => $block->starts_at->toIso8601String(),
                'end' => $block->ends_at->toIso8601String(),
                'units_blocked' => $block->units_blocked,
                'notes' => $block->notes,
            ])
            ->values();

        $locks = $this->availabilityService->standbyLockWindowsForCar($car->id)
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'source' => 'standby_lock',
                'start' => $order->pickup_at->toIso8601String(),
                'end' => $order->dropoff_at->toIso8601String(),
                'units_blocked' => 1,
                'expires_at' => $order->payment_lock_expires_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'booked' => $booked,
            'blocked' => $blocked->merge($locks)->values(),
        ]);
    }

    private function resolveSearchPricing(Car $car, Request $request): ?array
    {
        $pickupAtRaw = $request->query('pickup_at');
        $dropoffAtRaw = $request->query('dropoff_at');
        $pickupLocationId = $request->query('pickup_location_id');
        $dropoffLocationId = $request->query('dropoff_location_id');

        if (! $pickupAtRaw || ! $dropoffAtRaw || ! $pickupLocationId || ! $dropoffLocationId) {
            return null;
        }

        $priceTypeId = DailyFare::query()
            ->where('car_id', $car->id)
            ->orderBy('price_per_day_cents')
            ->value('price_type_id');

        if ($priceTypeId === null) {
            return null;
        }

        try {
            $quote = $this->quoteService->quote(
                $car,
                (int) $priceTypeId,
                Carbon::parse($pickupAtRaw),
                Carbon::parse($dropoffAtRaw),
                (int) $pickupLocationId,
                (int) $dropoffLocationId,
                [],
                null,
            );
        } catch (InvalidArgumentException) {
            return null;
        }

        return [
            'rental_days' => $quote['rental_days'],
            'total' => Money::formatDecimalFromCents($quote['total_cents']),
            'rental_subtotal' => Money::formatDecimalFromCents($quote['base_rental_cents']),
            'rental_before_specials' => Money::formatDecimalFromCents($quote['rental_before_specials_cents']),
            'special_discount_amount' => Money::formatDecimalFromCents($quote['special_discount_cents']),
            'special_surcharge_amount' => Money::formatDecimalFromCents($quote['special_surcharge_cents']),
            'fees_subtotal' => Money::formatDecimalFromCents($quote['fees_cents']),
            'fees_lines' => QuotePresentation::feesLines($quote['fees_lines']),
            'special_prices_applied' => array_map(
                fn (array $line) => [
                    'name' => $line['name'],
                    'type' => $line['type'],
                    'direction' => $line['direction'],
                    'is_promotion' => $line['is_promotion'],
                    'amount' => Money::formatDecimalFromCents($line['amount_cents']),
                ],
                $quote['special_prices_applied'],
            ),
            'has_special_discount' => $quote['special_discount_cents'] > 0,
            'currency' => $quote['currency'],
        ];
    }
}
