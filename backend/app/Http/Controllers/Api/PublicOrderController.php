<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Exceptions\BookingUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderQuoteRequest;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\Api\OrderResource;
use App\Models\Car;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\OrderRentalOption;
use App\Models\Setting;
use App\Services\Email\OrderEmailNotifier;
use App\Services\OrderAvailabilityService;
use App\Services\RentalQuoteService;
use App\Support\Money;
use App\Support\QuotePresentation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PublicOrderController extends Controller
{
    public function __construct(
        private readonly RentalQuoteService $quoteService,
        private readonly OrderAvailabilityService $availabilityService,
        private readonly OrderEmailNotifier $orderEmails,
    ) {}

    public function quote(OrderQuoteRequest $request): JsonResponse
    {
        $car = Car::query()->findOrFail($request->integer('car_id'));
        $pickup = Carbon::parse($request->string('pickup_at'));
        $dropoff = Carbon::parse($request->string('dropoff_at'));

        if (! $this->availabilityService->hasCapacity($car->id, $car->units_available, $pickup, $dropoff)) {
            return response()->json(['message' => 'No availability for these dates.'], 422);
        }

        try {
            $quote = $this->quoteService->quote(
                $car,
                $request->integer('price_type_id'),
                $pickup,
                $dropoff,
                $request->integer('pickup_location_id'),
                $request->integer('dropoff_location_id'),
                $request->input('rental_options', []),
                $request->input('coupon_code'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $split = $this->quoteService->splitRentalSubtotal(
            $car,
            $request->integer('price_type_id'),
            $pickup,
            $dropoff,
            $request->integer('pickup_location_id'),
            $request->integer('dropoff_location_id'),
            $request->input('rental_options', []),
            $request->input('coupon_code'),
            (int) $quote['base_rental_cents'],
        );

        return response()->json([
            'rental_subtotal' => Money::formatDecimalFromCents($quote['base_rental_cents']),
            'basic_rental_subtotal' => Money::formatDecimalFromCents($split['basic_rental_cents']),
            'protection_upgrade_subtotal' => Money::formatDecimalFromCents($split['protection_upgrade_cents']),
            'rental_before_specials' => Money::formatDecimalFromCents($quote['rental_before_specials_cents']),
            'special_discount_amount' => Money::formatDecimalFromCents($quote['special_discount_cents']),
            'special_surcharge_amount' => Money::formatDecimalFromCents($quote['special_surcharge_cents']),
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
            'fees_subtotal' => Money::formatDecimalFromCents($quote['fees_cents']),
            'fees_lines' => QuotePresentation::feesLines($quote['fees_lines']),
            'extras_subtotal' => Money::formatDecimalFromCents($quote['extras_cents']),
            'extras_lines' => QuotePresentation::extrasLines($quote['extras_lines']),
            'discount_amount' => Money::formatDecimalFromCents($quote['discount_cents']),
            'tax_amount' => Money::formatDecimalFromCents($quote['tax_cents']),
            'total' => Money::formatDecimalFromCents($quote['total_cents']),
            'currency' => $quote['currency'],
            'rental_days' => $quote['rental_days'],
            'base_rental_cents' => $quote['base_rental_cents'],
            'fees_cents' => $quote['fees_cents'],
            'extras_cents' => $quote['extras_cents'],
            'discount_cents' => $quote['discount_cents'],
            'tax_cents' => $quote['tax_cents'],
            'total_cents' => $quote['total_cents'],
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $car = Car::query()->findOrFail($request->integer('car_id'));
        $pickup = Carbon::parse($request->string('pickup_at'));
        $dropoff = Carbon::parse($request->string('dropoff_at'));

        if (! $this->availabilityService->hasCapacity($car->id, $car->units_available, $pickup, $dropoff)) {
            return response()->json(['message' => 'No availability for these dates.'], 422);
        }

        if (! $car->locations()->whereKey($request->integer('pickup_location_id'))->where('car_location.allows_pickup', true)->exists()) {
            return response()->json(['message' => 'Pick-up not allowed at this location for this vehicle.'], 422);
        }
        if (! $car->locations()->whereKey($request->integer('dropoff_location_id'))->where('car_location.allows_dropoff', true)->exists()) {
            return response()->json(['message' => 'Drop-off not allowed at this location for this vehicle.'], 422);
        }

        try {
            $quote = $this->quoteService->quote(
                $car,
                $request->integer('price_type_id'),
                $pickup,
                $dropoff,
                $request->integer('pickup_location_id'),
                $request->integer('dropoff_location_id'),
                $request->input('rental_options', []),
                $request->input('coupon_code'),
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        try {
            $order = DB::transaction(function () use ($request, $car, $pickup, $dropoff, $quote) {
            // Serialize concurrent bookings for this car so the capacity
            // re-check below is atomic and cannot be raced (double-booking).
            // Use the freshly locked row's fleet size, not the stale value read
            // before the transaction, in case units changed in the meantime.
            $lockedCar = Car::query()->whereKey($car->id)->lockForUpdate()->first();

            if (! $this->availabilityService->hasCapacity($car->id, (int) ($lockedCar->units_available ?? 0), $pickup, $dropoff)) {
                throw new BookingUnavailableException();
            }

            $lockMinutes = max(1, (int) data_get(Setting::getValue('shop.payment_lock_minutes', ['minutes' => 20]), 'minutes', 20));

            $order = Order::query()->create([
                'user_id' => $request->user()?->id,
                'car_id' => $car->id,
                'price_type_id' => $request->integer('price_type_id'),
                'pickup_location_id' => $request->integer('pickup_location_id'),
                'dropoff_location_id' => $request->integer('dropoff_location_id'),
                'pickup_at' => $pickup,
                'dropoff_at' => $dropoff,
                'order_status' => OrderStatus::Confirmed,
                'rental_status' => RentalStatus::Upcoming,
                'customer_name' => $request->string('customer_name'),
                'customer_email' => $request->string('customer_email'),
                'customer_phone' => $request->input('customer_phone'),
                'customer_country' => $request->input('customer_country'),
                'custom_field_values' => $this->sanitizeCustomFieldValues($request->input('custom_field_values', [])),
                'base_rental_cents' => $quote['base_rental_cents'],
                'extras_cents' => $quote['extras_cents'],
                'fees_cents' => $quote['fees_cents'],
                'discount_cents' => $quote['discount_cents'],
                'tax_cents' => $quote['tax_cents'],
                'total_cents' => $quote['total_cents'],
                'currency' => $quote['currency'],
                'coupon_id' => $quote['coupon_id'],
                'pricing_snapshot' => $quote,
                'payment_lock_expires_at' => now()->addMinutes($lockMinutes),
            ]);

            OrderLineItem::query()->create([
                'order_id' => $order->id,
                'kind' => 'base_rental',
                'label' => 'Vehicle rental',
                'amount_cents' => $quote['base_rental_cents'],
                'sort_order' => 0,
            ]);

            foreach ($quote['extras_lines'] as $i => $line) {
                OrderLineItem::query()->create([
                    'order_id' => $order->id,
                    'kind' => 'rental_option',
                    'label' => $line['name'],
                    'amount_cents' => $line['total_cents'],
                    'quantity' => $line['quantity'],
                    'sort_order' => $i + 1,
                ]);

                OrderRentalOption::query()->create([
                    'order_id' => $order->id,
                    'rental_option_id' => $line['rental_option_id'],
                    'quantity' => $line['quantity'],
                    'unit_price_cents' => (int) $line['unit_price_cents'],
                    'total_cents' => $line['total_cents'],
                ]);
            }

            foreach ($quote['fees_lines'] as $j => $feeLine) {
                OrderLineItem::query()->create([
                    'order_id' => $order->id,
                    'kind' => 'fee',
                    'label' => $feeLine['label'],
                    'amount_cents' => $feeLine['amount_cents'],
                    'sort_order' => 50 + $j,
                ]);
            }

            if ($quote['discount_cents'] > 0) {
                OrderLineItem::query()->create([
                    'order_id' => $order->id,
                    'kind' => 'discount',
                    'label' => 'Discount',
                    'amount_cents' => -$quote['discount_cents'],
                    'sort_order' => 90,
                ]);
            }

            if ($quote['tax_cents'] > 0) {
                OrderLineItem::query()->create([
                    'order_id' => $order->id,
                    'kind' => 'tax',
                    'label' => 'Tax',
                    'amount_cents' => $quote['tax_cents'],
                    'sort_order' => 100,
                ]);
            }

            if ($quote['coupon_id'] !== null) {
                $coupon = Coupon::query()->find($quote['coupon_id']);
                if ($coupon && $coupon->type === 'gift') {
                    CouponRedemption::query()->create([
                        'coupon_id' => $quote['coupon_id'],
                        'order_id' => $order->id,
                    ]);
                }
            }

            return $order;
            });
        } catch (BookingUnavailableException) {
            return response()->json(['message' => 'No availability for these dates.'], 422);
        }

        $this->orderEmails->notifyCreated($order->load('car.host'));

        return response()->json([
            'data' => OrderResource::make($order),
        ], 201);
    }

    /**
     * @param  mixed  $raw
     * @return array<string, string>
     */
    private function sanitizeCustomFieldValues(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $allowedKeys = \App\Models\CustomField::query()
            ->where('is_active', true)
            ->pluck('field_key')
            ->all();

        $sanitized = [];
        foreach ($allowedKeys as $key) {
            if (! array_key_exists($key, $raw)) {
                continue;
            }
            $value = $raw[$key];
            if ($value === null || $value === '') {
                continue;
            }
            $sanitized[$key] = (string) $value;
        }

        return $sanitized;
    }
}
