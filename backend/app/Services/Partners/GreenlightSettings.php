<?php

namespace App\Services\Partners;

use App\Models\Setting;

class GreenlightSettings
{
    public const PROVIDER = 'greenlight';

    public const DEFAULT_BASE_URL = 'https://greenlightcarrental.is/api/partner/v1';

    /**
     * @return array{enabled: bool, base_url: string, api_key: string, insurance_plan_ids: list<string>}
     */
    public function all(): array
    {
        $stored = Setting::getValue('partners.greenlight', []);

        $insurance = data_get($stored, 'insurance_plan_ids', []);

        return [
            'enabled' => (bool) data_get($stored, 'enabled', false),
            'base_url' => rtrim((string) data_get($stored, 'base_url', self::DEFAULT_BASE_URL), '/'),
            'api_key' => (string) data_get($stored, 'api_key', ''),
            'insurance_plan_ids' => is_array($insurance)
                ? array_values(array_filter(array_map('strval', $insurance)))
                : [],
        ];
    }

    public function enabled(): bool
    {
        return $this->apiKey() !== '';
    }

    public function baseUrl(): string
    {
        return $this->all()['base_url'] ?: self::DEFAULT_BASE_URL;
    }

    public function apiKey(): string
    {
        return $this->all()['api_key'];
    }

    /**
     * @return list<string>
     */
    public function insurancePlanIds(): array
    {
        return $this->all()['insurance_plan_ids'];
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function saveFromAdmin(array $state): void
    {
        $current = $this->all();
        $incomingKey = trim((string) ($state['greenlight_api_key'] ?? ''));

        $apiKey = $incomingKey !== '' ? $incomingKey : $current['api_key'];
        $enabled = (bool) ($state['greenlight_enabled'] ?? false);
        if ($apiKey !== '') {
            $enabled = true;
        }

        Setting::putValue('partners.greenlight', [
            'enabled' => $enabled,
            'base_url' => rtrim((string) ($state['greenlight_base_url'] ?? self::DEFAULT_BASE_URL), '/') ?: self::DEFAULT_BASE_URL,
            'api_key' => $apiKey,
            'insurance_plan_ids' => $current['insurance_plan_ids'],
        ]);
    }

    /**
     * @param  list<string>  $ids
     */
    public function storeInsurancePlanIds(array $ids): void
    {
        $current = $this->all();
        Setting::putValue('partners.greenlight', [
            ...$current,
            'insurance_plan_ids' => array_values(array_filter($ids)),
        ]);
    }
}
