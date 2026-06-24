<?php

namespace App\Services\Host;

use App\Enums\OrderStatus;
use App\Models\Car;
use App\Models\Order;
use App\Models\PriceType;
use App\Services\BookingChangeRequestService;
use App\Services\Order\OrderPricingSyncService;
use App\Support\DailyFarePricing;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class HostOrderModificationService
{
    public function __construct(
        private readonly BookingChangeRequestService $changeRequests,
        private readonly OrderPricingSyncService $pricingSync,
    ) {}

    /**
     * @return array{
     *     price_type_id: int,
     *     rental_option_ids: array<int, int>,
     *     available_price_types: array<int, array<string, mixed>>,
     *     available_rental_options: array<int, array<string, mixed>>,
     *     rental_days: int
     * }
     */
    public function optionsContext(Order $order): array
    {
        $order->loadMissing(['car.rentalOptions', 'rentalOptions']);
        $car = $order->car;
        if (! $car) {
            throw new InvalidArgumentException('Vehicle not found for this booking.');
        }

        $rentalDays = max(1, (int) $order->pickup_at->copy()->startOfDay()->diffInDays($order->dropoff_at->copy()->startOfDay()));

        return [
            'price_type_id' => (int) $order->price_type_id,
            'rental_option_ids' => $order->rentalOptions->pluck('rental_option_id')->map(fn ($id) => (int) $id)->all(),
            'available_price_types' => $this->availablePriceTypes($car),
            'available_rental_options' => $car->rentalOptions->map(fn ($option) => [
                'id' => $option->id,
                'name' => $option->name,
                'description' => $option->description,
                'cost_cents' => (int) ($option->pivot?->cost_cents ?? $option->cost_cents),
                'is_daily_cost' => (bool) ($option->pivot?->is_daily_cost ?? $option->is_daily_cost),
                'is_mandatory' => (bool) $option->is_mandatory,
            ])->values()->all(),
            'rental_days' => $rentalDays,
        ];
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array{quote: array<string, mixed>, total_formatted: string, price_delta_cents: int}
     */
    public function preview(Order $order, array $changes): array
    {
        $this->assertModifiable($order);

        return $this->changeRequests->previewOrderModification(
            $order,
            $this->normalizeChanges($order, $changes),
        );
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    public function apply(Order $order, array $changes): Order
    {
        $this->assertModifiable($order);

        return DB::transaction(function () use ($order, $changes) {
            $normalized = $this->normalizeChanges($order, $changes);
            $preview = $this->changeRequests->previewOrderModification($order, $normalized);
            $this->pricingSync->applyQuoteToOrder($order, $preview['quote'], $normalized);

            return $order->fresh([
                'car.subCategory',
                'pickupLocation',
                'dropoffLocation',
                'priceType',
                'lineItems',
                'rentalOptions.rentalOption',
                'changeRequests',
            ]);
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function availablePriceTypes(Car $car): array
    {
        $fromPriceByType = DailyFarePricing::fromPriceCentsByPriceTypeForCar($car->id);

        return PriceType::query()
            ->whereIn('id', array_keys($fromPriceByType))
            ->where('is_active', true)
            ->get()
            ->map(fn (PriceType $pt) => [
                'id' => $pt->id,
                'name' => $pt->name,
                'slug' => $pt->slug,
                'attribute_label' => $pt->attribute_label,
                'attribute_value_per_day' => $pt->attribute_value_per_day,
                'from_price_per_day_cents' => (int) ($fromPriceByType[$pt->id] ?? 0),
                'from_price_per_day' => Money::formatDecimalFromCents((int) ($fromPriceByType[$pt->id] ?? 0)),
            ])
            ->sortBy('from_price_per_day_cents')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function normalizeChanges(Order $order, array $changes): array
    {
        $order->loadMissing('car.rentalOptions');
        $car = $order->car;
        if (! $car) {
            throw new InvalidArgumentException('Vehicle not found for this booking.');
        }

        $normalized = [];

        if (array_key_exists('pickup_at', $changes) && $changes['pickup_at']) {
            $normalized['pickup_at'] = $changes['pickup_at'];
        }
        if (array_key_exists('dropoff_at', $changes) && $changes['dropoff_at']) {
            $normalized['dropoff_at'] = $changes['dropoff_at'];
        }
        if (array_key_exists('pickup_location_id', $changes) && $changes['pickup_location_id']) {
            $normalized['pickup_location_id'] = (int) $changes['pickup_location_id'];
        }
        if (array_key_exists('dropoff_location_id', $changes) && $changes['dropoff_location_id']) {
            $normalized['dropoff_location_id'] = (int) $changes['dropoff_location_id'];
        }

        if (array_key_exists('price_type_id', $changes) && $changes['price_type_id']) {
            $priceTypeId = (int) $changes['price_type_id'];
            $allowed = collect($this->availablePriceTypes($car))->pluck('id')->all();
            if (! in_array($priceTypeId, $allowed, true)) {
                throw new InvalidArgumentException('Selected protection plan is not available for this vehicle.');
            }
            $normalized['price_type_id'] = $priceTypeId;
        }

        if (array_key_exists('rental_options', $changes)) {
            $optionIds = collect($changes['rental_options'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values()
                ->all();
            $allowedOptionIds = $car->rentalOptions->pluck('id')->map(fn ($id) => (int) $id)->all();
            foreach ($optionIds as $optionId) {
                if (! in_array($optionId, $allowedOptionIds, true)) {
                    throw new InvalidArgumentException('One or more selected extras are not available for this vehicle.');
                }
            }
            $normalized['rental_options'] = $optionIds;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function mergeChanges(Order $order, array $base, array $overrides): array
    {
        return $this->normalizeChanges($order, array_merge($base, $overrides));
    }

    private function assertModifiable(Order $order): void
    {
        if ($order->order_status !== OrderStatus::Confirmed) {
            throw new InvalidArgumentException('Only confirmed bookings can be modified.');
        }
    }
}
