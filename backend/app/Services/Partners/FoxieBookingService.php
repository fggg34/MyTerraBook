<?php

namespace App\Services\Partners;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\Car;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\PriceType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class FoxieBookingService
{
    public function __construct(
        private readonly FoxiePartnerClient $client,
        private readonly FoxiePricingService $pricing,
    ) {}

    public function enabled(Car $car): bool
    {
        $host = $car->host;

        return $car->external_provider === 'foxie'
            && $car->isPubliclyVisible()
            && $host
            && $host->partner_provider === 'foxie'
            && $host->partner_api_enabled
            && filled($host->partner_api_key);
    }

    public static function priceType(): PriceType
    {
        return PriceType::query()->firstOrCreate(
            ['slug' => 'foxie-standard'],
            ['name' => 'Foxie rate', 'is_active' => true],
        );
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function quote(Car $car, array $input, bool $fresh = false): array
    {
        if (! $this->enabled($car)) {
            throw new InvalidArgumentException('Online booking is not enabled for this Foxie vehicle yet.');
        }
        if (! empty($input['rental_options']) || ! empty($input['coupon_code'])) {
            throw new InvalidArgumentException('Extra selections and discount codes are not supported for this Foxie rate.');
        }
        if (! PriceType::whereKey($input['price_type_id'] ?? 0)->where('slug', 'foxie-standard')->where('is_active', true)->exists()) {
            throw new InvalidArgumentException('Choose the Foxie rate.');
        }
        $from = $input['pickup_at'] instanceof Carbon ? $input['pickup_at'] : Carbon::parse((string) $input['pickup_at']);
        $to = $input['dropoff_at'] instanceof Carbon ? $input['dropoff_at'] : Carbon::parse((string) $input['dropoff_at']);
        $insuranceId = $this->selectedInsuranceId($car, $input['foxie_insurance_id'] ?? null);
        $result = $this->pricing->quote(
            $car,
            (int) $input['pickup_location_id'],
            (int) $input['dropoff_location_id'],
            $from,
            $to,
            $fresh,
            $insuranceId,
        );
        if (($result['status'] ?? null) === 'error') {
            throw new InvalidArgumentException('Foxie pricing could not be checked.');
        }
        $pricing = $result['pricing'] ?? null;
        if (($result['status'] ?? null) !== 'priced' || ! is_array($pricing)) {
            throw new InvalidArgumentException('Foxie has no availability for these dates.');
        }
        $total = $this->cents($pricing['total'] ?? null);
        $insuranceCents = $this->cents($pricing['insurance_total'] ?? 0);
        $days = (int) ($pricing['rental_days'] ?? 0);
        if ($total < 1 || $days < 1 || $insuranceCents > $total || ($pricing['currency'] ?? null) !== 'ISK') {
            throw new InvalidArgumentException('Foxie did not return a complete, bookable price.');
        }
        $base = $total - $insuranceCents;
        $insuranceName = trim((string) ($pricing['insurance_name'] ?? ''));
        if ($insuranceName === '') {
            $insuranceName = 'Insurance';
        }

        return [
            'rental_days' => $days, 'billable_days' => $days, 'pricing_mode' => 'foxie',
            'price_type_id' => (int) $input['price_type_id'], 'extra_hours_charged' => 0,
            'foxie_insurance_id' => $insuranceId,
            'base_rental_cents' => $base, 'rental_before_specials_cents' => $base,
            'special_discount_cents' => 0, 'special_surcharge_cents' => 0, 'special_prices_applied' => [],
            'fees_cents' => 0, 'fees_lines' => [],
            'extras_cents' => $insuranceCents,
            'extras_lines' => $insuranceCents > 0 ? [[
                'rental_option_id' => $insuranceId,
                'name' => $insuranceName,
                'quantity' => 1,
                'unit_price_cents' => $insuranceCents,
                'total_cents' => $insuranceCents,
            ]] : [],
            'discount_cents' => 0, 'tax_cents' => 0, 'total_cents' => $total,
            'currency' => 'ISK', 'coupon_id' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function create(Car $car, array $input, ?int $userId): Order
    {
        $quote = $this->quote($car, $input, true);
        $from = Carbon::parse((string) $input['pickup_at']);
        $to = Carbon::parse((string) $input['dropoff_at']);
        $query = $this->pricing->tripQuery(
            $car,
            (int) $input['pickup_location_id'],
            (int) $input['dropoff_location_id'],
            $from,
            $to,
            $quote['foxie_insurance_id'] ?? null,
        );
        if ($query === null) {
            throw new InvalidArgumentException('Foxie has no availability for these dates.');
        }
        $parts = preg_split('/\s+/', trim((string) $input['customer_name']), 2) ?: [];
        $first = trim((string) ($parts[0] ?? ''));
        $last = trim((string) ($parts[1] ?? ''));
        if ($first === '') {
            throw new InvalidArgumentException('A customer name is required.');
        }
        if ($last === '') {
            $last = $first;
        }
        $reference = Order::generateUniqueReference();
        try {
            $remote = $this->client->book($car->host, $query + [
                'partner_ref' => $reference,
                'customer' => [
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => (string) $input['customer_email'],
                    'phone' => (string) ($input['customer_phone'] ?? ''),
                ],
            ]);
        } catch (Throwable $e) {
            throw new InvalidArgumentException($e instanceof InvalidArgumentException ? $e->getMessage() : 'Foxie could not complete this reservation. Please try again.');
        }
        $bookingId = (int) $remote['booking_id'];
        $quote['foxie'] = [
            'booking_id' => $bookingId,
            'status' => $remote['status'] ?? null,
            'paid_online' => $remote['paid_online'] ?? null,
            'paid_amount' => $remote['paid_amount'] ?? null,
            'balance_at_pickup' => $remote['balance_at_pickup'] ?? null,
            'currency' => $remote['currency'] ?? $remote['paid_currency'] ?? 'ISK',
        ];
        try {
            return DB::transaction(function () use ($car, $input, $userId, $quote, $reference, $bookingId, $from, $to) {
                $order = Order::query()->create([
                    'reference' => $reference,
                    'user_id' => $userId,
                    'car_id' => $car->id,
                    'price_type_id' => $input['price_type_id'],
                    'pickup_location_id' => $input['pickup_location_id'],
                    'dropoff_location_id' => $input['dropoff_location_id'],
                    'pickup_at' => $from,
                    'dropoff_at' => $to,
                    'order_status' => OrderStatus::Confirmed,
                    'rental_status' => RentalStatus::Upcoming,
                    'customer_name' => $input['customer_name'],
                    'customer_email' => $input['customer_email'],
                    'customer_phone' => $input['customer_phone'] ?? null,
                    'customer_country' => $input['customer_country'] ?? null,
                    'customer_date_of_birth' => $input['customer_date_of_birth'] ?? null,
                    'custom_field_values' => is_array($input['custom_field_values'] ?? null) ? $input['custom_field_values'] : null,
                    'external_provider' => 'foxie',
                    'external_reference' => (string) $bookingId,
                    'partner_sync_state' => 'confirmed',
                    'base_rental_cents' => $quote['base_rental_cents'],
                    'extras_cents' => $quote['extras_cents'],
                    'fees_cents' => 0,
                    'discount_cents' => 0,
                    'tax_cents' => 0,
                    'total_cents' => $quote['total_cents'],
                    'currency' => 'ISK',
                    'pricing_snapshot' => $quote,
                ]);
                if ($quote['base_rental_cents'] > 0) {
                    OrderLineItem::query()->create([
                        'order_id' => $order->id,
                        'kind' => 'base_rental',
                        'label' => 'Vehicle rental',
                        'amount_cents' => $quote['base_rental_cents'],
                        'sort_order' => 0,
                    ]);
                }
                foreach ($quote['extras_lines'] as $index => $line) {
                    OrderLineItem::query()->create([
                        'order_id' => $order->id,
                        'kind' => 'fee',
                        'label' => $line['name'],
                        'amount_cents' => $line['total_cents'],
                        'sort_order' => $index + 1,
                    ]);
                }

                return $order;
            });
        } catch (Throwable $e) {
            try {
                $this->client->cancelBooking($car->host, $bookingId);
            } catch (Throwable) {
                // The local order was not saved. The Foxie hold still needs a manual cancel if this also failed.
            }

            throw $e;
        }
    }

    public function cancel(Order $order): void
    {
        if ($order->external_provider !== 'foxie' || ! preg_match('/^[1-9]\d*$/', (string) $order->external_reference)) {
            return;
        }
        $order->loadMissing('car.host');
        $host = $order->car?->host;
        if (! $host) {
            throw new InvalidArgumentException('Foxie booking could not be cancelled.');
        }
        $this->client->cancelBooking($host, (int) $order->external_reference);
    }

    private function selectedInsuranceId(Car $car, mixed $raw): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (! is_numeric($raw) || (int) $raw < 1) {
            throw new InvalidArgumentException('Choose a Foxie insurance plan from this vehicle.');
        }
        $id = (int) $raw;
        $plans = $this->pricing->insurancePlans($car);
        if ($plans === []) {
            throw new InvalidArgumentException('Foxie insurance plans could not be checked.');
        }
        foreach ($plans as $plan) {
            if ($plan['id'] === $id) {
                return $id;
            }
        }

        throw new InvalidArgumentException('Choose a Foxie insurance plan from this vehicle.');
    }

    private function cents(mixed $amount): int
    {
        if (! is_numeric($amount) || ! is_finite((float) $amount) || (float) $amount <= 0) {
            return 0;
        }

        return (int) round((float) $amount * 100);
    }
}
