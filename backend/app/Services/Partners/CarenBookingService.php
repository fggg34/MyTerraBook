<?php

namespace App\Services\Partners;

use App\Enums\OrderStatus;
use App\Enums\RentalStatus;
use App\Models\Car;
use App\Models\Order;
use App\Models\OrderLineItem;
use App\Models\PriceType;
use App\Models\RapydPayment;
use App\Models\Setting;
use App\Models\User;
use App\Services\Email\OrderEmailNotifier;
use App\Support\PricingCurrency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class CarenBookingService
{
    public function __construct(private readonly CarenPartnerClient $client) {}

    public function enabled(Car $car): bool
    {
        $rentalId = explode(':', (string) $car->external_vehicle_id)[0];

        return (bool) config('services.caren.bookings_enabled') && $car->external_provider === 'caren'
            && $car->isPubliclyVisible() && $car->host?->partner_provider === 'caren'
            && $car->host?->partner_api_enabled
            && (int) config('services.caren.payment_options.'.$rentalId, 0) > 0;
    }

    public function quote(Car $car, array $input): array
    {
        if (! $this->enabled($car)) {
            throw new InvalidArgumentException('Online booking is not enabled for this Caren rental yet.');
        }
        if (! empty($input['rental_options']) || ! empty($input['coupon_code'])) {
            throw new InvalidArgumentException('Extra selections and discount codes are not supported for this Caren rate.');
        }
        if (! PriceType::whereKey($input['price_type_id'] ?? 0)->where('slug', 'caren-standard')->where('is_active', true)->exists()) {
            throw new InvalidArgumentException('Choose the Caren standard rate.');
        }
        $payload = $this->trip($car, $input);
        try {
            $available = $this->client->bookingRequest($car->host, 'class/checkavailable', $payload);
            if (($available['Available'] ?? false) !== true || (int) ($available['NumberOfUnits'] ?? 0) < 1) {
                throw new InvalidArgumentException('Caren has no availability for these dates.');
            }
            $prices = $this->client->bookingRequest($car->host, 'price/getprices', $payload);
            $options = $this->client->bookingRequest($car->host, 'payment/getoptions', $payload);
        } catch (Throwable $e) {
            throw new InvalidArgumentException($e instanceof InvalidArgumentException ? $e->getMessage() : 'Caren could not verify this booking. Please try again.');
        }
        if (! collect($options['PaymentOptions'] ?? [])->contains(fn ($option) => (int) ($option['Id'] ?? 0) === $payload['PaymentOptionId'])) {
            throw new InvalidArgumentException('The configured Caren payment option is not available for this trip.');
        }
        if ((int) ($prices['VehicleClassId'] ?? 0) !== $payload['ClassId']
            || ($prices['Currency'] ?? null) !== $payload['Currency']) {
            throw new InvalidArgumentException('Caren returned an incompatible price or currency.');
        }
        $base = $this->cents($prices['VehicleTotalPrice'] ?? null);
        $fees = $this->cents($prices['LocationTotalPrice'] ?? null);
        $extras = $this->cents($prices['ExtrasTotalPrice'] ?? null) + $this->cents($prices['InsurancesTotalPrice'] ?? null);
        $total = $this->cents($prices['TotalPrice'] ?? null);
        $days = (int) ($prices['NumberOfDays'] ?? 0);
        if ($base <= 0 || $total <= 0 || $days < 1 || abs($total - $base - $fees - $extras) > 1) {
            throw new InvalidArgumentException('Caren did not return a complete, bookable price.');
        }
        if ((int) round(round($total / 100, \App\Services\RapydService::decimalsFor($payload['Currency'])) * 100) !== $total) {
            throw new InvalidArgumentException('Caren returned a price precision unsupported by the payment gateway.');
        }

        return [
            'rental_days' => $days, 'billable_days' => $days, 'pricing_mode' => 'caren',
            'price_type_id' => (int) $input['price_type_id'], 'extra_hours_charged' => 0,
            'base_rental_cents' => $base, 'rental_before_specials_cents' => $base,
            'special_discount_cents' => 0, 'special_surcharge_cents' => 0, 'special_prices_applied' => [],
            'fees_cents' => $fees, 'fees_lines' => $fees ? [['label' => 'Caren location fees', 'amount_cents' => $fees]] : [],
            'extras_cents' => $extras, 'extras_lines' => $extras ? [['name' => 'Caren included extras and insurance',
                'quantity' => 1, 'unit_price_cents' => $extras, 'total_cents' => $extras]] : [],
            'discount_cents' => 0, 'tax_cents' => 0, 'total_cents' => $total,
            'currency' => $payload['Currency'], 'coupon_id' => null,
            'caren' => ['host_id' => $car->user_id, 'origin' => $car->host->partner_api_base_url ?: CarenPartnerClient::BASE_URL,
                'trip' => $payload],
        ];
    }

    private function trip(Car $car, array $input): array
    {
        if (! preg_match('/^([1-9]\d*):([1-9]\d*)$/', (string) $car->external_vehicle_id, $ids)) {
            throw new InvalidArgumentException('This vehicle is not mapped to Caren.');
        }
        $rentalId = (int) $ids[1];
        $from = Carbon::parse($input['pickup_at']);
        $to = Carbon::parse($input['dropoff_at']);
        if ($from->isPast() || $to->lessThanOrEqualTo($from)) {
            throw new InvalidArgumentException('Choose future pick-up and drop-off dates.');
        }
        $locations = [];
        foreach (['pickup' => 'allows_pickup', 'dropoff' => 'allows_dropoff'] as $key => $permission) {
            $location = $car->locations()->where('locations.id', $input[$key.'_location_id'])
                ->where('locations.is_active', true)->where('host_user_id', $car->user_id)
                ->where('external_provider', 'caren')->wherePivot($permission, true)->first();
            if (! $location || ! preg_match('/^'.$rentalId.':([1-9]\d*)$/', (string) $location->external_id, $mapped)) {
                throw new InvalidArgumentException('The selected location is not available for this Caren vehicle.');
            }
            $locations[$key] = (int) $mapped[1];
        }

        return ['RentalId' => $rentalId, 'ClassId' => (int) $ids[2],
            'DateFrom' => $from->utc()->format('Y-m-d H:i'), 'DateTo' => $to->utc()->format('Y-m-d H:i'),
            'PickupLocationId' => $locations['pickup'], 'DropoffLocationId' => $locations['dropoff'],
            'Currency' => PricingCurrency::shopDefault(), 'Language' => 'en-GB',
            'PaymentOptionId' => (int) config('services.caren.payment_options.'.$rentalId),
            'Extras' => [], 'Insurances' => [], 'SharedLocation' => false];
    }

    public function create(Car $car, array $input, ?int $userId): Order
    {
        $token = $input['checkout_token'];
        return Cache::lock('caren-create:'.$token, 180)->block(5, function () use ($car, $input, $userId, $token) {
            $fingerprint = hash('sha256', json_encode([$userId, $input]));
            $existing = Order::where('partner_checkout_token', $token)->first();
            if ($existing) {
                if (data_get($existing->pricing_snapshot, 'caren.request_hash') !== $fingerprint) {
                    throw new InvalidArgumentException('This checkout attempt has changed. Start a new checkout.');
                }
                if (in_array($existing->partner_sync_state, ['reserved', 'payment_recorded', 'confirm_pending', 'confirmed'], true)
                    && ($existing->order_status === OrderStatus::Confirmed || $existing->payment_lock_expires_at?->isFuture())) {
                    return $existing;
                }
                throw new InvalidArgumentException('This reservation attempt needs review. Contact support with reference '.$existing->reference.'. Do not submit another booking.');
            }
            $quote = $this->quote($car, $input); // Always live; never reuse the search-price cache.
            if ($quote['total_cents'] !== (int) $input['expected_total_cents'] || $quote['currency'] !== $input['expected_currency']) {
                throw new InvalidArgumentException('The Caren price changed. Refresh your quote before booking.');
            }
            $quote['caren']['request_hash'] = $fingerprint;
            $lockMinutes = max(1, (int) data_get(Setting::getValue('shop.payment_lock_minutes', ['minutes' => 20]), 'minutes', 20));
            $order = DB::transaction(function () use ($car, $input, $userId, $token, $quote, $lockMinutes) {
                $order = Order::create([
                    'user_id' => $userId, 'car_id' => $car->id, 'price_type_id' => $input['price_type_id'],
                    'pickup_location_id' => $input['pickup_location_id'], 'dropoff_location_id' => $input['dropoff_location_id'],
                    'pickup_at' => $input['pickup_at'], 'dropoff_at' => $input['dropoff_at'],
                    'order_status' => OrderStatus::Pending, 'rental_status' => null,
                    'customer_name' => $input['customer_name'], 'customer_email' => $input['customer_email'],
                    'customer_phone' => $input['customer_phone'], 'customer_country' => $input['customer_country'] ?? null,
                    'customer_date_of_birth' => $input['customer_date_of_birth'],
                    'external_provider' => 'caren', 'partner_checkout_token' => $token, 'partner_sync_state' => 'creating',
                    'base_rental_cents' => $quote['base_rental_cents'], 'extras_cents' => $quote['extras_cents'],
                    'fees_cents' => $quote['fees_cents'], 'discount_cents' => 0, 'tax_cents' => 0,
                    'total_cents' => $quote['total_cents'], 'currency' => $quote['currency'], 'pricing_snapshot' => $quote,
                    'payment_lock_expires_at' => now()->addMinutes($lockMinutes),
                ]);
                foreach ([['Vehicle rental', $quote['base_rental_cents']], ['Caren location fees', $quote['fees_cents']],
                    ['Caren included extras and insurance', $quote['extras_cents']]] as $index => [$label, $amount]) {
                    if ($amount > 0) {
                        OrderLineItem::create(['order_id' => $order->id, 'kind' => $index === 0 ? 'base_rental' : 'fee',
                            'label' => $label, 'amount_cents' => $amount, 'sort_order' => $index]);
                    }
                }

                return $order;
            });
            $parts = preg_split('/\s+/', trim($input['customer_name']), 2);
            try {
                $remote = $this->client->bookingRequest($car->host, 'reservation/add', $quote['caren']['trip'] + [
                    'Reference' => $order->reference, 'confirmReservation' => false,
                    'Drivers' => [[0, $input['customer_name'], $input['customer_license_number'], $input['customer_date_of_birth'], true]],
                    'Customer' => ['FirstName' => $parts[0], 'LastName' => $parts[1] ?? $parts[0],
                        'Email' => $input['customer_email'], 'Mobile' => $input['customer_phone']],
                    'RentalComments' => 'TerraBook '.$order->reference.'; awaiting online payment.',
                ]);
                if (! Str::isUuid($remote['Guid'] ?? '')) {
                    throw new InvalidArgumentException('Caren did not return a reservation identifier.');
                }
                $order->update(['external_reference' => $remote['Guid'], 'partner_sync_state' => 'verifying']);
                $reservation = $this->client->bookingRequest($car->host, 'reservation/getitem', $this->reservationPayload($order));
                if (($reservation['Guid'] ?? '') !== $order->external_reference
                    || ($reservation['Currency'] ?? '') !== $order->currency
                    || $this->cents($reservation['TotalPrice'] ?? null) !== $order->total_cents
                    || (int) data_get($reservation, 'Vehicle.ClassId') !== $quote['caren']['trip']['ClassId']
                    || (int) data_get($reservation, 'Rental.Id') !== $quote['caren']['trip']['RentalId']
                    || (int) data_get($reservation, 'Location.PickupId') !== $quote['caren']['trip']['PickupLocationId']
                    || (int) data_get($reservation, 'Location.DropoffId') !== $quote['caren']['trip']['DropoffLocationId']
                    || empty($reservation['DateFrom']) || empty($reservation['DateTo'])
                    || Carbon::parse($reservation['DateFrom'])->format('Y-m-d H:i') !== $quote['caren']['trip']['DateFrom']
                    || Carbon::parse($reservation['DateTo'])->format('Y-m-d H:i') !== $quote['caren']['trip']['DateTo']) {
                    $this->cancel($order);
                    throw new InvalidArgumentException('Caren returned different reservation details. No payment was started.');
                }
                $snapshot = $order->pricing_snapshot;
                $snapshot['caren']['booking_code'] = $reservation['BookingCode'] ?? null;
                $order->update(['partner_sync_state' => 'reserved', 'pricing_snapshot' => $snapshot]);
            } catch (Throwable $e) {
                if ($order->partner_sync_state === 'creating') {
                    $this->fail($order, 'create_unknown', 'Reservation result unknown; reconcile by TerraBook reference.');
                } elseif ($order->partner_sync_state === 'verifying') {
                    $this->fail($order, 'verify_pending', 'Reservation needs verification before payment.');
                }
                throw new InvalidArgumentException('Caren could not complete this reservation. Contact support with reference '.$order->reference.'. No payment was started.');
            }

            return $order->fresh();
        });
    }

    public function confirmPaid(Order $order, RapydPayment $payment): void
    {
        Cache::lock('caren-order:'.$order->id, 180)->block(5, function () use ($order, $payment) {
            $order->refresh();
            $payment->refresh();
            if ($order->partner_sync_state === 'confirmed') {
                return;
            }
            if ($payment->status !== 'paid' || (int) $payment->order_id !== $order->id
                || data_get($payment->metadata, 'order_type') !== 'car' || $payment->currency !== $order->currency
                || $this->cents($payment->total_price) !== $order->total_cents
                || $this->cents($payment->platform_fee) <= 0 || $this->cents($payment->platform_fee) > $order->total_cents) {
                throw new InvalidArgumentException('Payment does not match the Caren order.');
            }
            if (! in_array($order->partner_sync_state, ['reserved', 'payment_recorded', 'confirm_pending'], true)
                || $order->order_status !== OrderStatus::Pending) {
                Log::warning('Caren paid order needs manual review', ['order_id' => $order->id, 'state' => $order->partner_sync_state]);
                return;
            }
            $host = $this->host($order);
            if ($order->partner_sync_state === 'reserved') {
                // Persist before the write; never repeat an ambiguous prepaid credit.
                $order->update(['partner_sync_state' => 'payment_recording']);
                try {
                    $result = $this->client->bookingRequest($host, 'payment/addprepaid', $this->reservationPayload($order) + [
                        'Amount' => (float) $payment->platform_fee,
                        'Reference' => 'TerraBook Rapyd '.($payment->payment_id ?: $payment->checkout_id),
                    ]);
                    if (! Str::isUuid($result['Guid'] ?? '') || ($result['Currency'] ?? '') !== $order->currency
                        || $this->cents($result['Amount'] ?? null) !== $this->cents($payment->platform_fee)) {
                        throw new InvalidArgumentException('Unverified prepaid credit response.');
                    }
                    $snapshot = $order->pricing_snapshot;
                    $snapshot['caren']['prepaid_guid'] = $result['Guid'];
                    $order->update(['partner_sync_state' => 'payment_recorded', 'pricing_snapshot' => $snapshot, 'partner_sync_error' => null]);
                } catch (Throwable) {
                    $this->fail($order, 'payment_unknown', 'Rapyd payment received; Caren prepaid credit must be reconciled before retry.');
                    return;
                }
            }
            $order->update(['partner_sync_state' => 'confirm_pending']);
            try {
                $result = $this->client->bookingRequest($host, 'reservation/confirm', $this->reservationPayload($order) + [
                    'CheckPayment' => true, 'SendConfirmationMail' => false,
                ]);
                if (($result['Success'] ?? false) !== true) {
                    throw new InvalidArgumentException('Caren did not confirm the reservation.');
                }
                $order->forceFill(['partner_sync_state' => 'confirmed', 'partner_sync_error' => null,
                    'order_status' => OrderStatus::Confirmed, 'rental_status' => RentalStatus::Upcoming,
                    'payment_lock_expires_at' => null])->saveQuietly();
            } catch (Throwable) {
                $this->fail($order, 'confirm_pending', 'Payment recorded; Caren confirmation will be retried.');
                return;
            }
            try {
                app(OrderEmailNotifier::class)->notifyCreated($order->load('car.host'));
            } catch (Throwable) {
                Log::warning('Caren confirmation email failed', ['order_id' => $order->id]);
            }
        });
    }

    public function cancel(Order $order, bool $onlyIfExpiredAndUnpaid = false): void
    {
        Cache::lock('caren-order:'.$order->id, 180)->block(5, function () use ($order, $onlyIfExpiredAndUnpaid) {
            $order->refresh();
            if ($onlyIfExpiredAndUnpaid && ($order->order_status !== OrderStatus::Pending
                || ! $order->payment_lock_expires_at?->isPast()
                || RapydPayment::where('order_id', $order->id)->where('metadata->order_type', 'car')->where('status', 'paid')->exists())) {
                return;
            }
            if ($order->partner_sync_state === 'cancelled') {
                return;
            }
            if (! filled($order->external_reference)) {
                throw new InvalidArgumentException('The Caren reservation must be reconciled before cancellation.');
            }
            $order->update(['partner_sync_state' => 'cancel_pending']);
            try {
                $result = $this->client->bookingRequest($this->host($order), 'reservation/cancel', $this->reservationPayload($order));
                if (($result['Success'] ?? false) !== true) {
                    throw new InvalidArgumentException('Cancellation not acknowledged.');
                }
                $previousStatus = $order->order_status;
                $order->forceFill(['partner_sync_state' => 'cancelled', 'partner_sync_error' => null,
                    'order_status' => OrderStatus::Cancelled, 'rental_status' => null, 'payment_lock_expires_at' => null])->saveQuietly();
            } catch (Throwable) {
                $this->fail($order, 'cancel_pending', 'Caren cancellation needs retry; reservation is not yet cancelled.');
                throw new InvalidArgumentException('Caren has not confirmed cancellation. Please retry.');
            }
            try {
                app(OrderEmailNotifier::class)->notifyStatusChanged($order, $previousStatus, OrderStatus::Cancelled);
            } catch (Throwable) {
                Log::warning('Caren cancellation email failed', ['order_id' => $order->id]);
            }
        });
    }

    private function host(Order $order): User
    {
        $host = User::find(data_get($order->pricing_snapshot, 'caren.host_id'));
        if (! $host || ($host->partner_api_base_url ?: CarenPartnerClient::BASE_URL) !== data_get($order->pricing_snapshot, 'caren.origin')) {
            throw new InvalidArgumentException('The original Caren connection is required to update this reservation.');
        }

        return $host;
    }

    private function reservationPayload(Order $order): array
    {
        return ['RentalId' => (int) data_get($order->pricing_snapshot, 'caren.trip.RentalId'),
            'Guid' => $order->external_reference, 'Language' => 'en-GB'];
    }

    private function cents(mixed $amount): int
    {
        if (! is_numeric($amount) || ! is_finite((float) $amount) || $amount < 0) {
            throw new InvalidArgumentException('Caren returned an invalid amount.');
        }

        return (int) round((float) $amount * 100);
    }

    private function fail(Order $order, string $state, string $message): void
    {
        $order->update(['partner_sync_state' => $state, 'partner_sync_error' => $message]);
        Log::warning('Caren reservation needs attention', ['order_id' => $order->id, 'state' => $state]);
    }
}
