<?php

namespace App\Services\Partners;

use App\Models\Car;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CarenOptionsService
{
    public function __construct(private readonly CarenPartnerClient $client) {}

    /**
     * @return array{insurance_plans: list<array<string, mixed>>, partner_extras: list<array<string, mixed>>, partner_characteristics: list<array{name: string}>}
     */
    public function forCar(Car $car): array
    {
        $empty = ['insurance_plans' => [], 'partner_extras' => [], 'partner_characteristics' => []];
        $host = $car->host;
        $classId = $this->classId($car);
        if (! $host || $classId === null || ! $this->connected($host)) {
            return $empty;
        }

        try {
            $bundle = $this->bundle($host);
        } catch (Throwable) {
            return $empty;
        }

        return $this->present($bundle, $classId);
    }

    /**
     * @param  list<string>  $extraIds
     * @return array{extras: list<array{Id: int, Quantity: int}>, insurances: list<array{Id: int}>}
     */
    public function selection(Car $car, array $extraIds, ?string $insuranceId): array
    {
        $options = $this->forCar($car);
        $extras = [];
        foreach ($extraIds as $raw) {
            $id = $this->numericId($raw);
            if ($id === null || ! $this->contains($options['partner_extras'], $id)) {
                throw new \InvalidArgumentException('Choose extras from this vehicle.');
            }
            $extras[] = ['Id' => $id, 'Quantity' => 1];
        }
        $insurances = [];
        if ($insuranceId !== null && $insuranceId !== '') {
            $id = $this->numericId($insuranceId);
            if ($id === null || ! $this->contains($options['insurance_plans'], $id)) {
                throw new \InvalidArgumentException('Choose a Caren insurance plan from this vehicle.');
            }
            $insurances[] = ['Id' => $id];
        }

        return ['extras' => $extras, 'insurances' => $insurances];
    }

    private function connected(User $host): bool
    {
        return $host->partner_provider === 'caren'
            && $host->partner_api_enabled
            && filled($host->partner_api_key)
            && filled($host->partner_api_username)
            && filled($host->partner_api_password);
    }

    private function classId(Car $car): ?int
    {
        if ($car->external_provider !== 'caren' || ! preg_match('/^[1-9]\d*:([1-9]\d*)$/', (string) $car->external_vehicle_id, $match)) {
            return null;
        }

        return (int) $match[1];
    }

    /**
     * @return array{classes: list<array<string, mixed>>, extras: list<array<string, mixed>>, insurances: list<array<string, mixed>>}
     */
    private function bundle(User $host): array
    {
        $key = 'caren-options:'.hash('sha256', json_encode([
            $host->id,
            $host->partner_api_key,
            $host->partner_rental_id,
        ]));
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }
        $bundle = $this->client->options($host);
        Cache::put($key, $bundle, 600);

        return $bundle;
    }

    /**
     * @param  array{classes: list<array<string, mixed>>, extras: list<array<string, mixed>>, insurances: list<array<string, mixed>>}  $bundle
     * @return array{insurance_plans: list<array<string, mixed>>, partner_extras: list<array<string, mixed>>, partner_characteristics: list<array{name: string}>}
     */
    private function present(array $bundle, int $classId): array
    {
        $class = null;
        foreach ($bundle['classes'] as $row) {
            if ((int) ($row['Id'] ?? 0) === $classId) {
                $class = $row;
                break;
            }
        }

        $characteristics = [];
        if (is_array($class)) {
            foreach ($this->classCharacteristics($class) as $name) {
                $row = PartnerOfferFormatter::characteristic($name);
                if ($row) {
                    $characteristics[] = $row;
                }
            }
        }

        $extras = [];
        foreach ($bundle['extras'] as $row) {
            if (! $this->appliesToClass($row, $classId) || filter_var($row['Included'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                if (filter_var($row['Included'] ?? false, FILTER_VALIDATE_BOOLEAN) && $this->appliesToClass($row, $classId)) {
                    $included = PartnerOfferFormatter::characteristic((string) ($row['Name'] ?? ''));
                    if ($included) {
                        $characteristics[] = $included;
                    }
                }

                continue;
            }
            $offer = $this->pricedOffer($row, 'Extra');
            if ($offer) {
                $extras[] = $offer;
            }
        }

        $plans = [];
        foreach ($bundle['insurances'] as $row) {
            if (! $this->appliesToClass($row, $classId)) {
                continue;
            }
            $offer = $this->pricedOffer($row, 'Insurance');
            if ($offer) {
                $plans[] = $offer;
            }
        }

        return [
            'insurance_plans' => $plans,
            'partner_extras' => $extras,
            'partner_characteristics' => $this->uniqueCharacteristics($characteristics),
        ];
    }

    /**
     * @param  array<string, mixed>  $class
     * @return list<string>
     */
    private function classCharacteristics(array $class): array
    {
        $rows = [];
        $group = trim((string) ($class['GroupName'] ?? ''));
        if ($group !== '') {
            $rows[] = $group;
        }
        $doors = (int) ($class['Doors'] ?? 0);
        if ($doors > 0) {
            $rows[] = $doors.' doors';
        }
        if (filter_var($class['AirCondition'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $rows[] = 'Air conditioning';
        }
        if (filter_var($class['ForHighland'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $rows[] = 'Highland ready';
        }
        $age = (int) ($class['MinimumDriverAge'] ?? 0);
        if ($age > 0) {
            $rows[] = 'Minimum driver age '.$age;
        }
        $acriss = trim((string) ($class['ACRISS'] ?? ''));
        if ($acriss !== '') {
            $rows[] = 'ACRISS '.$acriss;
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>|null
     */
    private function pricedOffer(array $row, string $fallback): ?array
    {
        $id = $this->numericId($row['Id'] ?? null);
        if ($id === null) {
            return null;
        }
        $price = $row['Price'] ?? 0;
        if (! is_numeric($price) || ! is_finite((float) $price) || (float) $price < 0) {
            return null;
        }
        $currency = strtoupper(trim((string) ($row['Currency'] ?? 'ISK')));
        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            $currency = 'ISK';
        }
        $max = (int) ($row['MaximumQuantity'] ?? 0);
        $name = trim((string) ($row['Name'] ?? ''));

        return PartnerOfferFormatter::offer(
            (string) $id,
            $name !== '' ? $name : $fallback,
            (string) ($row['Description'] ?? ''),
            (int) round((float) $price * 100),
            filter_var($row['PricePerDay'] ?? false, FILTER_VALIDATE_BOOLEAN),
            $currency,
            $max > 0 ? $max : null,
        );
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function appliesToClass(array $row, int $classId): bool
    {
        $ids = $row['ClassIds'] ?? [];
        if (! is_array($ids) || $ids === []) {
            return true;
        }
        foreach ($ids as $id) {
            if ((int) $id === $classId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function contains(array $rows, int $id): bool
    {
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return true;
            }
        }

        return false;
    }

    private function numericId(mixed $raw): ?int
    {
        if (! is_numeric($raw) || (int) $raw < 1) {
            return null;
        }

        return (int) $raw;
    }

    /**
     * @param  list<array{name: string}>  $rows
     * @return list<array{name: string}>
     */
    private function uniqueCharacteristics(array $rows): array
    {
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
}
