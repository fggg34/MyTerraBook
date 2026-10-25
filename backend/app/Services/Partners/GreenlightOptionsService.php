<?php

namespace App\Services\Partners;

use App\Exceptions\GreenlightPartnerException;
use App\Models\Car;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GreenlightOptionsService
{
    public function __construct(
        private readonly GreenlightPartnerClient $client,
        private readonly GreenlightSettings $settings,
    ) {}

    /**
     * @return array{insurance_plans: list<array<string, mixed>>, partner_extras: list<array<string, mixed>>, partner_characteristics: list<array{name: string}>}
     */
    public function forCar(Car $car): array
    {
        $empty = ['insurance_plans' => [], 'partner_extras' => [], 'partner_characteristics' => []];
        if ($car->external_provider !== GreenlightSettings::PROVIDER || ! filled($car->external_vehicle_id) || ! $this->settings->enabled()) {
            return $empty;
        }

        try {
            $bundle = $this->bundle();
        } catch (Throwable) {
            return $empty;
        }

        return $this->present($bundle, (string) $car->external_vehicle_id);
    }

    /**
     * @param  list<string>  $addonIds
     * @return list<array{addonId: string, quantity: int}>
     */
    public function selectedAddons(Car $car, array $addonIds): array
    {
        if ($addonIds === []) {
            return [];
        }
        $options = $this->forCar($car);
        if ($options['partner_extras'] === []) {
            throw new GreenlightPartnerException('Greenlight extras could not be checked.');
        }
        $selected = [];
        foreach ($addonIds as $raw) {
            $id = trim((string) $raw);
            if ($id === '' || ! $this->contains($options['partner_extras'], $id)) {
                throw new GreenlightPartnerException('Choose extras from this vehicle.');
            }
            $selected[] = ['addonId' => $id, 'quantity' => 1];
        }

        return $selected;
    }

    /**
     * @return array{vehicles: list<array<string, mixed>>, plans: list<array<string, mixed>>, addons: list<array<string, mixed>>}
     */
    private function bundle(): array
    {
        $key = 'greenlight-options:'.hash('sha256', $this->settings->baseUrl().'|'.$this->settings->apiKey());
        $cached = Cache::get($key);
        if (is_array($cached)) {
            return $cached;
        }
        $bundle = [
            'vehicles' => $this->client->vehicles(),
            'plans' => $this->client->insurancePlans(),
            'addons' => $this->client->addons(),
        ];
        Cache::put($key, $bundle, 600);

        return $bundle;
    }

    /**
     * @param  array{vehicles: list<array<string, mixed>>, plans: list<array<string, mixed>>, addons: list<array<string, mixed>>}  $bundle
     * @return array{insurance_plans: list<array<string, mixed>>, partner_extras: list<array<string, mixed>>, partner_characteristics: list<array{name: string}>}
     */
    private function present(array $bundle, string $vehicleId): array
    {
        $product = null;
        foreach ($bundle['vehicles'] as $row) {
            if ((string) ($row['id'] ?? '') === $vehicleId) {
                $product = $row;
                break;
            }
        }
        $categoryId = is_array($product) ? (string) ($product['categoryId'] ?? '') : '';
        $characteristics = [];
        foreach (is_array($product) ? ($product['features'] ?? []) : [] as $feature) {
            $name = is_array($feature) ? (string) ($feature['name'] ?? '') : (string) $feature;
            $row = PartnerOfferFormatter::characteristic($name);
            if ($row) {
                $characteristics[] = $row;
            }
        }

        $plans = [];
        foreach ($bundle['plans'] as $plan) {
            $id = trim((string) ($plan['id'] ?? ''));
            if ($id === '') {
                continue;
            }
            $daily = $plan['dailyPriceIsk'] ?? 0;
            if (! is_numeric($daily) || (float) $daily < 0) {
                continue;
            }
            $labels = [];
            foreach ($plan['options'] ?? [] as $option) {
                if (! is_array($option) || ! filter_var($option['included'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                    continue;
                }
                $label = trim((string) ($option['label'] ?? ''));
                if ($label !== '') {
                    $labels[] = $label;
                }
            }
            $plans[] = PartnerOfferFormatter::offer(
                $id,
                trim((string) ($plan['name'] ?? '')) ?: 'Insurance',
                implode(', ', $labels),
                (int) round((float) $daily * 100),
                true,
            );
        }

        $extras = [];
        foreach ($bundle['addons'] as $addon) {
            $categories = $addon['categoryIds'] ?? [];
            if ($categoryId !== '' && is_array($categories) && $categories !== [] && ! in_array($categoryId, $categories, true)) {
                continue;
            }
            $id = trim((string) ($addon['id'] ?? ''));
            $daily = $addon['dailyPriceIsk'] ?? 0;
            if ($id === '' || ! is_numeric($daily) || (float) $daily < 0) {
                continue;
            }
            $extras[] = PartnerOfferFormatter::offer(
                $id,
                trim((string) ($addon['name'] ?? '')) ?: 'Extra',
                (string) ($addon['description'] ?? ''),
                (int) round((float) $daily * 100),
                filter_var($addon['chargePerDay'] ?? false, FILTER_VALIDATE_BOOLEAN),
            );
        }

        return [
            'insurance_plans' => $plans,
            'partner_extras' => $extras,
            'partner_characteristics' => $characteristics,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function contains(array $rows, string $id): bool
    {
        foreach ($rows as $row) {
            if ((string) ($row['id'] ?? '') === $id) {
                return true;
            }
        }

        return false;
    }
}
