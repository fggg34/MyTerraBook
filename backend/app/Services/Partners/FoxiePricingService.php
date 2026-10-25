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
    public function quote(Car $car, int $pickupId, int $dropoffId, CarbonInterface $from, CarbonInterface $to, bool $fresh = false, ?int $insuranceId = null, array $extraIds = []): array
    {
        $unavailable = ['status' => 'unavailable', 'pricing' => null];
        if ($insuranceId !== null && ! $this->hasInsurancePlan($car, $insuranceId)) {
            return ['status' => 'error', 'pricing' => null];
        }
        $extraIds = $this->normalizeExtraIds($extraIds);
        if ($extraIds !== [] && ! $this->hasExtras($car, $extraIds)) {
            return ['status' => 'error', 'pricing' => null];
        }
        $query = $this->tripQuery($car, $pickupId, $dropoffId, $from, $to, $insuranceId);
        if ($query !== null && $extraIds !== []) {
            $query['extras'] = $this->extrasQuery($extraIds);
        }
        if ($query === null) {
            return $unavailable;
        }
        $host = $car->host;
        $key = 'foxie-price:'.hash('sha256', json_encode([
            $host->id, $host->partner_api_key, $query,
        ]));
        if (! $fresh && array_key_exists($key, $this->results)) {
            return $this->results[$key];
        }
        try {
            $result = $fresh
                ? $this->fetch($host, $query, $from, $to)
                : Cache::remember($key, 60, fn () => $this->fetch($host, $query, $from, $to));
        } catch (Throwable) {
            $result = ['status' => 'error', 'pricing' => null];
        }
        if (! $fresh) {
            $this->results[$key] = $result;
        }

        return $result;
    }

    /**
     * Undated starting price from Foxie price_from. Zero is not a price.
     *
     * @return array{status: string, pricing: array<string, mixed>|null}
     */
    public function starting(Car $car): array
    {
        $empty = ['status' => 'needs_dates', 'pricing' => null];
        $host = $car->host;
        if (! $host || $car->external_provider !== 'foxie' || ! $host->partner_api_enabled
            || $host->partner_provider !== 'foxie'
            || ! preg_match('/^[1-9]\d*$/', (string) $car->external_vehicle_id)) {
            return $empty;
        }
        $vehicleId = (int) $car->external_vehicle_id;
        $key = 'foxie-start:'.hash('sha256', json_encode([$host->id, $host->partner_api_key, $vehicleId]));
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return ['status' => 'base', 'pricing' => $cached];
        }
        try {
            $pricing = $this->startingFromVehicle($host, $vehicleId);
        } catch (Throwable) {
            return $empty;
        }
        if ($pricing === null) {
            return $empty;
        }
        Cache::put($key, $pricing, 600);

        return ['status' => 'base', 'pricing' => $pricing];
    }

    /**
     * @return array<string, int|string>|null
     */
    /**
     * @return list<array{id: int, name: string, description: string, cost: string, cost_cents: int, per_day: bool, currency: string}>
     */
    public function insurancePlans(Car $car): array
    {
        $host = $car->host;
        if (! $host || $car->external_provider !== 'foxie' || ! $host->partner_api_enabled
            || $host->partner_provider !== 'foxie'
            || ! preg_match('/^[1-9]\d*$/', (string) $car->external_vehicle_id)) {
            return [];
        }
        try {
            $detail = $this->vehicleDetail($host, (int) $car->external_vehicle_id);
        } catch (Throwable) {
            return [];
        }
        $rows = $detail['insurance'] ?? null;
        if (! is_array($rows)) {
            return [];
        }
        $plans = [];
        foreach ($rows as $row) {
            if (! is_array($row) || ($row['is_insurance'] ?? true) !== true) {
                continue;
            }
            $id = $row['id'] ?? null;
            if (! is_numeric($id) || (int) $id < 1) {
                continue;
            }
            $major = $row['cost'] ?? 0;
            if (! is_numeric($major) || ! is_finite((float) $major) || (float) $major < 0) {
                continue;
            }
            $cents = (int) round((float) $major * 100);
            $name = trim((string) ($row['name'] ?? ''));
            $plans[] = [
                'id' => (int) $id,
                'name' => $name !== '' ? $name : 'Insurance',
                'description' => trim((string) ($row['description'] ?? '')),
                'cost' => Money::formatDecimalFromCents($cents),
                'cost_cents' => $cents,
                'per_day' => filter_var($row['per_day'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'currency' => 'ISK',
            ];
        }

        return $plans;
    }

    /**
     * @return list<array{id: int, name: string, description: string, cost: string, cost_cents: int, per_day: bool, max_price_cents: int, currency: string}>
     */
    public function extras(Car $car): array
    {
        $detail = $this->detailFor($car);
        $rows = $detail['extras'] ?? null;
        if (! is_array($rows)) {
            return [];
        }
        $extras = [];
        foreach ($rows as $row) {
            if (! is_array($row) || filter_var($row['is_insurance'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }
            $id = $row['id'] ?? null;
            $major = $row['cost'] ?? 0;
            if (! is_numeric($id) || (int) $id < 1 || ! is_numeric($major) || ! is_finite((float) $major) || (float) $major < 0) {
                continue;
            }
            $cents = (int) round((float) $major * 100);
            $max = $row['max_price'] ?? 0;
            $name = trim((string) ($row['name'] ?? ''));
            $extras[] = [
                'id' => (int) $id,
                'name' => $name !== '' ? $name : 'Extra',
                'description' => trim((string) ($row['description'] ?? '')),
                'cost' => Money::formatDecimalFromCents($cents),
                'cost_cents' => $cents,
                'per_day' => filter_var($row['per_day'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_daily_cost' => filter_var($row['per_day'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'max_quantity' => max(1, (int) ($row['max_quantity'] ?? 1)),
                'max_price_cents' => is_numeric($max) && (float) $max > 0 ? (int) round((float) $max * 100) : 0,
                'currency' => 'ISK',
            ];
        }

        return $extras;
    }

    /**
     * @return list<array{name: string}>
     */
    public function characteristics(Car $car): array
    {
        $detail = $this->detailFor($car);
        $rows = [];
        $chars = $detail['characteristics'] ?? [];
        if (is_array($chars)) {
            foreach ($chars as $item) {
                $name = $this->characteristicName($item);
                if ($name !== null) {
                    $row = PartnerOfferFormatter::characteristic($name);
                    if ($row) {
                        $rows[] = $row;
                    }
                }
            }
        }
        $doors = (int) ($detail['doors'] ?? 0);
        if ($doors > 0) {
            $row = PartnerOfferFormatter::characteristic($doors.' doors');
            if ($row) {
                $rows[] = $row;
            }
        }
        $seen = [];
        $unique = [];
        foreach ($rows as $row) {
            $key = mb_strtolower($row['name']);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $row;
        }

        return $unique;
    }

    /**
     * @param  list<int>  $extraIds
     * @return list<array{id: int, name: string, total_cents: int}>
     */
    public function extraLines(Car $car, array $extraIds, int $days): array
    {
        $catalog = [];
        foreach ($this->extras($car) as $extra) {
            $catalog[$extra['id']] = $extra;
        }
        $lines = [];
        foreach ($extraIds as $id) {
            $extra = $catalog[$id] ?? null;
            if (! $extra || $days < 1) {
                return [];
            }
            $cents = $extra['per_day'] ? $extra['cost_cents'] * $days : $extra['cost_cents'];
            if ($extra['max_price_cents'] > 0 && $cents > $extra['max_price_cents']) {
                $cents = $extra['max_price_cents'];
            }
            $lines[] = ['id' => $id, 'name' => $extra['name'], 'total_cents' => $cents];
        }

        return $lines;
    }

    public function hasInsurancePlan(Car $car, int $insuranceId): bool
    {
        foreach ($this->insurancePlans($car) as $plan) {
            if ($plan['id'] === $insuranceId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<int>  $extraIds
     */
    public function hasExtras(Car $car, array $extraIds): bool
    {
        $known = [];
        foreach ($this->extras($car) as $extra) {
            $known[$extra['id']] = true;
        }
        foreach ($extraIds as $id) {
            if (! isset($known[$id])) {
                return false;
            }
        }

        return $extraIds !== [];
    }

    /**
     * @param  list<int>  $extraIds
     */
    public function extrasQuery(array $extraIds): string
    {
        return implode(';', array_map(static fn (int $id) => $id.':1', $extraIds));
    }

    public function tripQuery(Car $car, int $pickupId, int $dropoffId, CarbonInterface $from, CarbonInterface $to, ?int $insuranceId = null): ?array
    {
        $host = $car->host;
        if (! $host || $car->external_provider !== 'foxie' || ! $host->partner_api_enabled
            || $host->partner_provider !== 'foxie' || $to->lessThanOrEqualTo($from) || $from->copy()->utc()->isPast()) {
            return null;
        }
        if (! preg_match('/^[1-9]\d*$/', (string) $car->external_vehicle_id)) {
            return null;
        }
        $locations = $car->locations()->where('locations.is_active', true)
            ->where('external_provider', 'foxie')->where('host_user_id', $host->id)
            ->whereIn('locations.id', [$pickupId, $dropoffId])->get()->keyBy('id');
        $pickup = $locations->get($pickupId);
        $dropoff = $locations->get($dropoffId);
        if (! $pickup?->pivot?->allows_pickup || ! $dropoff?->pivot?->allows_dropoff
            || ! preg_match('/^[1-9]\d*$/', (string) $pickup->external_id)
            || ! preg_match('/^[1-9]\d*$/', (string) $dropoff->external_id)) {
            return null;
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
        if ($insuranceId !== null && $insuranceId > 0) {
            $query['insurance'] = $insuranceId;
        }

        return $query;
    }

    /**
     * @param  array<string, scalar>  $query
     * @return array{status: string, pricing: array<string, mixed>|null}
     */
    private function fetch(User $host, array $query, CarbonInterface $from, CarbonInterface $to): array
    {
        $unavailable = ['status' => 'unavailable', 'pricing' => null];
        $availabilityQuery = $query;
        unset($availabilityQuery['extras']);
        $availability = $this->client->availability($host, $availabilityQuery);
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
        $insuranceMajor = $body['insurance_total'] ?? 0;
        $insuranceCents = is_numeric($insuranceMajor) && is_finite((float) $insuranceMajor) && (float) $insuranceMajor > 0
            ? (int) round((float) $insuranceMajor * 100)
            : 0;
        if ($insuranceCents > $cents) {
            return $unavailable;
        }
        $insuranceName = null;
        $insuranceRows = $body['insurance'] ?? null;
        if (is_array($insuranceRows)) {
            foreach ($insuranceRows as $row) {
                if (is_array($row) && filled($row['name'] ?? null)) {
                    $insuranceName = trim((string) $row['name']);
                    break;
                }
            }
        }

        return ['status' => 'priced', 'pricing' => [
            'source' => 'foxie',
            'estimate_only' => true,
            'rental_days' => $dayCount,
            'currency' => $currency,
            'rental_subtotal' => Money::formatDecimalFromCents($cents),
            'total' => Money::formatDecimalFromCents($cents),
            'daily_price' => Money::formatDecimalFromCents((int) round($cents / $dayCount)),
            'insurance_total' => Money::formatDecimalFromCents($insuranceCents),
            'insurance_name' => $insuranceName,
            'extras_total' => Money::formatDecimalFromCents($this->majorCents($body['extras_total'] ?? 0)),
            'has_special_discount' => false,
        ]];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function startingFromVehicle(User $host, int $vehicleId): ?array
    {
        $detail = $this->vehicleDetail($host, $vehicleId);
        $amount = $detail['price_from'] ?? null;
        $span = $detail['price_from_per_days'] ?? 1;
        $currency = array_key_exists('currency', $detail)
            ? PricingCurrency::normalize(is_string($detail['currency']) ? $detail['currency'] : null)
            : 'ISK';
        if (! $currency || ! is_numeric($amount) || ! is_finite((float) $amount) || (float) $amount <= 0
            || ! is_numeric($span) || (int) $span < 1) {
            return null;
        }
        $cents = (int) round(((float) $amount / (int) $span) * 100);
        if ($cents < 1) {
            return null;
        }

        return [
            'source' => 'foxie',
            'estimate_only' => true,
            'base_only' => true,
            'rental_days' => 1,
            'currency' => $currency,
            'rental_subtotal' => Money::formatDecimalFromCents($cents),
            'total' => Money::formatDecimalFromCents($cents),
            'daily_price' => Money::formatDecimalFromCents($cents),
            'has_special_discount' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function vehicleDetail(User $host, int $vehicleId): array
    {
        $key = 'foxie-vehicle:'.hash('sha256', json_encode([$host->id, $host->partner_api_key, $vehicleId]));
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }
        $detail = $this->client->vehicle($host, $vehicleId);
        Cache::put($key, $detail, 600);

        return $detail;
    }

    /**
     * @return array<string, mixed>
     */
    private function detailFor(Car $car): array
    {
        $host = $car->host;
        if (! $host || $car->external_provider !== 'foxie' || ! $host->partner_api_enabled
            || $host->partner_provider !== 'foxie'
            || ! preg_match('/^[1-9]\d*$/', (string) $car->external_vehicle_id)) {
            return [];
        }
        try {
            return $this->vehicleDetail($host, (int) $car->external_vehicle_id);
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param  list<mixed>  $raw
     * @return list<int>
     */
    private function normalizeExtraIds(array $raw): array
    {
        $ids = [];
        foreach ($raw as $value) {
            if (! is_numeric($value) || (int) $value < 1) {
                return [-1];
            }
            $ids[] = (int) $value;
        }

        return array_values(array_unique($ids));
    }

    private function characteristicName(mixed $item): ?string
    {
        if (is_string($item) || is_numeric($item)) {
            $name = trim((string) $item);

            return $name !== '' ? $name : null;
        }
        if (! is_array($item)) {
            return null;
        }
        foreach (['name', 'label', 'title'] as $key) {
            if (filled($item[$key] ?? null) && ! is_array($item[$key])) {
                return trim((string) $item[$key]);
            }
        }

        return null;
    }

    private function majorCents(mixed $amount): int
    {
        if (! is_numeric($amount) || ! is_finite((float) $amount) || (float) $amount <= 0) {
            return 0;
        }

        return (int) round((float) $amount * 100);
    }
}
