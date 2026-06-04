<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CarDetailResource;
use App\Http\Resources\Api\CarListResource;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\LocationResource;
use App\Models\BookingRestriction;
use App\Models\Car;
use App\Models\Category;
use App\Models\DailyFare;
use App\Models\Location;
use App\Models\Order;
use App\Models\PriceType;
use App\Services\OrderAvailabilityService;
use App\Services\RentalQuoteService;
use App\Support\Money;
use App\Support\QuotePresentation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CatalogController extends Controller
{
    public function __construct(
        private readonly OrderAvailabilityService $availabilityService,
        private readonly RentalQuoteService $quoteService,
    ) {}

    public function categories(): JsonResponse
    {
        $rows = Category::query()->where('is_active', true)->orderBy('sort_order')->get();

        return response()->json(['data' => CategoryResource::collection($rows)]);
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
        ]);
    }

    public function cars(Request $request): JsonResponse
    {
        $pickupId = $request->query('pickup_location_id');
        $dropoffId = $request->query('dropoff_location_id');

        $query = Car::query()
            ->where('is_active', true)
            ->with('category');

        if ($pickupId) {
            $query->whereHas('locations', fn ($q) => $q->where('locations.id', $pickupId)->where('car_location.allows_pickup', true));
        }
        if ($dropoffId) {
            $query->whereHas('locations', fn ($q) => $q->where('locations.id', $dropoffId)->where('car_location.allows_dropoff', true));
        }

        $minPrices = DailyFare::query()
            ->select('car_id', DB::raw('MIN(price_per_day_cents) as min_daily_price_cents'))
            ->groupBy('car_id');

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
                'category_id' => $car->category_id,
                'category_name' => $car->category?->name,
                'transmission' => $car->transmission,
                'fuel_type' => $car->fuel_type,
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
        $car->load([
            'category',
            'characteristics',
            'rentalOptions',
            'listingReviews' => fn ($q) => $q->approved()->latest()->limit(50),
        ]);

        $fareMins = DailyFare::query()
            ->where('car_id', $car->id)
            ->selectRaw('price_type_id, MIN(price_per_day_cents) as min_cents')
            ->groupBy('price_type_id')
            ->pluck('min_cents', 'price_type_id');

        $priceTypes = PriceType::query()
            ->whereIn('id', $fareMins->keys())
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (PriceType $pt) => [
                'id' => $pt->id,
                'name' => $pt->name,
                'slug' => $pt->slug,
                'attribute_label' => $pt->attribute_label,
                'attribute_value_per_day' => $pt->attribute_value_per_day,
                'from_price_per_day_cents' => (int) $fareMins->get($pt->id, 0),
            ])
            ->values()
            ->all();

        return response()->json([
            'data' => new CarDetailResource($car, $priceTypes),
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
