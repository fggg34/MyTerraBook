<?php

namespace App\Services\Partners;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class GreenlightSettings
{
    public const PROVIDER = 'greenlight';

    public const DEFAULT_BASE_URL = 'https://greenlightcarrental.is/api/partner/v1';

    /**
     * @return array{enabled: bool, base_url: string, api_key: string, insurance_plan_ids: list<string>, display_name: string, logo_path: string}
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
            'display_name' => trim((string) data_get($stored, 'display_name', 'Greenlight car rental')) ?: 'Greenlight car rental',
            'logo_path' => (string) data_get($stored, 'logo_path', ''),
        ];
    }

    public function displayName(): string
    {
        return $this->all()['display_name'];
    }

    public function logoUrl(): ?string
    {
        $path = $this->all()['logo_path'];
        if ($path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
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

        $displayName = trim((string) ($state['greenlight_display_name'] ?? $current['display_name']));

        Setting::putValue('partners.greenlight', [
            'enabled' => $enabled,
            'base_url' => rtrim((string) ($state['greenlight_base_url'] ?? self::DEFAULT_BASE_URL), '/') ?: self::DEFAULT_BASE_URL,
            'api_key' => $apiKey,
            'insurance_plan_ids' => $current['insurance_plan_ids'],
            'display_name' => $displayName !== '' ? $displayName : 'Greenlight car rental',
            'logo_path' => (string) ($state['greenlight_logo_path'] ?? $current['logo_path']),
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
