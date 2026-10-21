<?php

namespace App\Services\Partners;

use App\Models\Car;
use App\Models\User;
use App\Support\Money;
use App\Support\PricingCurrency;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Throwable;

class FoxiePricingService
{
    /** @var array<string, array{status: string, pricing: array<string, mixed>|null}> */
    private array $results = [];

    public function __construct(private readonly FoxiePartnerClient $client) {}

    /**
     * @return array{status: string, pricing: array<string, mixed>|null}
     */
    public function quote(Car $car, int $pickupId, int $dropoffId, CarbonInterface $from, CarbonInterface $to): array
    {
        $unavailable = ['status' => 'unavailable', 'pricing' => null];
        $host = $car->host;
        if (! $host || $car->external_provider !== 'foxie' || ! $host->partner_api_enabled
            || $host->partner_provider !== 'foxie' || $to->lessThanOrEqualTo($from) || $from->copy()->utc()->isPast()) {
            return $unavailable;
        }
        if (! preg_match('/^[1-9]\d*$/', (string) $car->external_vehicle_id)) {
            return $unavailable;
        }
        $locations = $car->locations()->where('locations.is_active', true)
            ->where('external_provider', 'foxie')->where('host_user_id', $host->id)
            ->whereIn('locations.id', [$pickupId, $dropoffId])->get()->keyBy('id');
        $pickup = $locations->get($pickupId);
        $dropoff = $locations->get($dropoffId);
        if (! $pickup?->pivot?->allows_pickup || ! $dropoff?->pivot?->allows_dropoff
            || ! preg_match('/^[1-9]\d*$/', (string) $pickup->external_id)
            || ! preg_match('/^[1-9]\d*$/', (string) $dropoff->external_id)) {
            return $unavailable;
        }

        $query = [
            'vehicle_id' => (int) $car->external_vehicle_id,
            'pickup_date' => $from->copy()->utc()->format('Y-m-d'),
            'dropoff_date' => $to->copy()->utc()->format('Y-m-d'),
            'pickup_time' => $from->copy()->utc()->format('H:i'),
            'dropoff_time' => $to->copy()->utc()->format('H:i'),
            'pickup_location' => (int) $pickup->external_id,
            'dropoff_location' => (int) $dropoff->external_id,
        ];
        $key = 'foxie-price:'.hash('sha256', json_encode([
            $host->id, $host->partner_api_key, $query,
        ]));
        if (! array_key_exists($key, $this->results)) {
            try {
                $this->results[$key] = Cache::remember($key, 60, fn () => $this->fetch($host, $query, $from, $to));
            } catch (Throwable) {
                $this->results[$key] = ['status' => 'error', 'pricing' => null];
            }
        }

        return $this->results[$key];
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array{status: string, pricing: array<string, mixed>|null}
     */
    private function fetch(User $host, array $query, CarbonInterface $from, CarbonInterface $to): array
    {
        $unavailable = ['status' => 'unavailable', 'pricing' => null];
        $availability = $this->client->availability($host, $query);
        if (($availability['available'] ?? null) !== true) {
            return $unavailable;
        }
        try {
            $body = $this->client->pricing($host, $query);
        } catch (FoxieNoRateException) {
            return $unavailable;
        }
        $total = $body['total'] ?? $body['total_price'] ?? $body['price'] ?? $body['amount'] ?? null;
        $currency = array_key_exists('currency', $body)
            ? PricingCurrency::normalize(is_string($body['currency']) ? $body['currency'] : null)
            : 'ISK';
        $days = $body['days'] ?? $body['rental_days'] ?? null;
        if (! is_numeric($days) || (int) $days < 1) {
            $days = $from->copy()->utc()->startOfDay()->diffInDays($to->copy()->utc()->startOfDay());
        }
        if (! $currency || ! is_numeric($total) || ! is_finite((float) $total) || (float) $total <= 0 || (int) $days < 1) {
            if (! is_numeric($total) && ! array_key_exists('total', $body) && ! array_key_exists('total_price', $body)
                && ! array_key_exists('price', $body) && ! array_key_exists('amount', $body)) {
                throw new \RuntimeException('Foxie pricing could not be checked.');
            }

            return $unavailable;
        }
        $cents = (int) round((float) $total * 100);
        $dayCount = (int) $days;

        return ['status' => 'priced', 'pricing' => [
            'source' => 'foxie',
            'estimate_only' => true,
            'rental_days' => $dayCount,
            'currency' => $currency,
            'rental_subtotal' => Money::formatDecimalFromCents($cents),
            'total' => Money::formatDecimalFromCents($cents),
            'daily_price' => Money::formatDecimalFromCents((int) round($cents / $dayCount)),
            'has_special_discount' => false,
        ]];
    }
}
