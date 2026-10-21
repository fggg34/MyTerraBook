<?php

namespace App\Services\Partners;

use App\Models\Car;
use App\Support\Money;
use App\Support\PricingCurrency;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CarenPricingService
{
    // Request-local memoization also prevents repeated calls when the provider fails.
    private array $results = [];

    public function __construct(private readonly CarenPartnerClient $client) {}

    public function quote(Car $car, int $pickupId, int $dropoffId, CarbonInterface $from, CarbonInterface $to): array
    {
        $unavailable = ['status' => 'unavailable', 'pricing' => null];
        $host = $car->host;
        if (! $host || $car->external_provider !== 'caren' || ! $host->partner_api_enabled
            || $host->partner_provider !== 'caren' || $from->isPast() || $to->lessThanOrEqualTo($from)) {
            return $unavailable;
        }
        if (! preg_match('/^(\d+):(\d+)$/', (string) $car->external_vehicle_id, $match)) {
            return $unavailable;
        }
        $rentalId = (int) $match[1];
        $classId = (int) $match[2];
        $locations = $car->locations()->where('locations.is_active', true)
            ->where('external_provider', 'caren')->where('host_user_id', $host->id)
            ->whereIn('locations.id', [$pickupId, $dropoffId])->get()->keyBy('id');
        $pickup = $locations->get($pickupId);
        $dropoff = $locations->get($dropoffId);
        if (! $pickup?->pivot?->allows_pickup || ! $dropoff?->pivot?->allows_dropoff) {
            return $unavailable;
        }
        $externalIds = [];
        foreach ([$pickup, $dropoff] as $location) {
            if (! preg_match('/^'.preg_quote((string) $rentalId, '/').':([1-9]\d*)$/', (string) $location->external_id, $ids)) {
                return $unavailable;
            }
            $externalIds[] = (int) $ids[1];
        }
        $currency = PricingCurrency::shopDefault();
        $key = 'caren-prices:'.hash('sha256', json_encode([
            $host->id, $host->partner_api_base_url, $host->partner_api_username,
            $host->partner_api_password, $host->partner_api_key, $rentalId, $externalIds,
            $from->toIso8601String(), $to->toIso8601String(), $currency,
        ]));
        if (! array_key_exists($key, $this->results)) {
            try {
                $this->results[$key] = Cache::remember($key, 60, fn () => $this->client->prices(
                    $host, $rentalId, $externalIds[0], $externalIds[1], $from, $to, $currency,
                ));
            } catch (Throwable) {
                $this->results[$key] = null;
            }
        }
        if ($this->results[$key] === null) {
            return ['status' => 'error', 'pricing' => null];
        }
        $row = collect($this->results[$key])->first(fn ($row) => is_array($row) && (int) ($row['Id'] ?? 0) === $classId);
        if (! $row || ($row['Available'] ?? false) !== true) {
            return $unavailable;
        }
        // Never relabel a provider currency or treat a missing/zero rate as free.
        $returnedCurrency = PricingCurrency::normalize($row['Currency'] ?? null);
        $total = $row['TotalPrice'] ?? null;
        $days = $row['Days'] ?? null;
        if (! $returnedCurrency || ! is_numeric($total) || ! is_finite((float) $total) || $total <= 0
            || ! is_numeric($days) || $days <= 0 || $days != (int) $days) {
            return $unavailable;
        }
        $cents = (int) round((float) $total * 100);

        return ['status' => 'priced', 'pricing' => [
            'source' => 'caren', 'estimate_only' => true,
            'rental_days' => (int) $days, 'currency' => $returnedCurrency,
            'rental_subtotal' => Money::formatDecimalFromCents($cents),
            'total' => Money::formatDecimalFromCents($cents),
            'daily_price' => Money::formatDecimalFromCents((int) round($cents / $days)),
            'has_special_discount' => false,
        ]];
    }
}
