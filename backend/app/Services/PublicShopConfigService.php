<?php

namespace App\Services;

use App\Models\Setting;

class PublicShopConfigService
{
    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        $currency = Setting::getValue('shop.currency', [
            'name' => 'Euro',
            'symbol' => '€',
            'code' => 'EUR',
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousand_separator' => ',',
        ]);

        $deposit = Setting::getValue('shop.deposit', [
            'value' => 15,
            'type' => 'percentage',
        ]);

        $depositType = (string) data_get($deposit, 'type', 'percentage');
        $depositValue = (int) data_get($deposit, 'value', 15);

        $baseCode = (string) data_get($currency, 'code', 'EUR');

        return [
            'maps_api_key' => (string) data_get(
                Setting::getValue('system.google_maps_api_key', ['key' => '']),
                'key',
                '',
            ),
            'currency' => [
                'code' => $baseCode,
                'symbol' => (string) data_get($currency, 'symbol', '€'),
                'name' => (string) data_get($currency, 'name', 'Euro'),
                'decimals' => (int) data_get($currency, 'decimals', 2),
                'decimal_separator' => (string) data_get($currency, 'decimal_separator', '.'),
                'thousand_separator' => (string) data_get($currency, 'thousand_separator', ','),
            ],
            'deposit' => [
                'allow_deposit' => (bool) data_get(Setting::getValue('shop.allow_deposit', ['enabled' => true]), 'enabled', true),
                'value' => $depositValue,
                'type' => $depositType,
            ],
            'prepay_percent' => $depositType === 'percentage' ? $depositValue : null,
            'rentals_enabled' => (bool) data_get(Setting::getValue('shop.rentals_enabled', ['enabled' => true]), 'enabled', true),
            'enable_coupons' => (bool) data_get(Setting::getValue('shop.enable_coupons', ['enabled' => true]), 'enabled', true),
            'payment_lock_minutes' => (int) data_get(Setting::getValue('shop.payment_lock_minutes', ['minutes' => 20]), 'minutes', 20),
            'minimum_rental_days' => (int) data_get(Setting::getValue('shop.minimum_rental_days', ['days' => 1]), 'days', 1),
            'exchange_rates' => $this->exchangeRates($baseCode),
        ];
    }

    /**
     * @return array<string, float>
     */
    private function exchangeRates(string $baseCode): array
    {
        $stored = Setting::getValue('shop.exchange_rates', []);

        $defaults = [
            'EUR' => 1.0,
            'USD' => 1.08,
            'GBP' => 0.86,
            'ISK' => 150.0,
        ];

        $rates = is_array($stored) ? $stored : [];
        $merged = array_merge($defaults, $rates);

        if (! isset($merged[$baseCode])) {
            $merged[$baseCode] = 1.0;
        }

        $baseRate = (float) $merged[$baseCode];
        if ($baseRate <= 0) {
            $baseRate = 1.0;
        }

        $normalized = [];
        foreach ($merged as $code => $rate) {
            $normalized[(string) $code] = round((float) $rate / $baseRate, 6);
        }

        $normalized[$baseCode] = 1.0;

        return $normalized;
    }
}
