<?php

namespace App\Services\Partners;

use App\Models\Setting;

class GreenlightMoney
{
    /**
     * Convert Greenlight ISK integers into MyTerra shop-currency cents.
     *
     * @return array{cents: int, currency: string, isk: int}
     */
    public function fromIsk(int $isk): array
    {
        $currency = (string) data_get(Setting::getValue('shop.currency', ['code' => 'ISK']), 'code', 'ISK');
        $currency = $currency !== '' ? strtoupper($currency) : 'ISK';

        if ($currency === 'ISK') {
            return [
                'cents' => $isk * 100,
                'currency' => 'ISK',
                'isk' => $isk,
            ];
        }

        $rates = Setting::getValue('shop.exchange_rates', []);
        $defaults = ['EUR' => 1.0, 'USD' => 1.08, 'GBP' => 0.86, 'ISK' => 150.0];
        $merged = array_merge($defaults, is_array($rates) ? $rates : []);
        $iskPerBase = (float) ($merged['ISK'] ?? 150.0);
        $shopPerBase = (float) ($merged[$currency] ?? 1.0);
        if ($iskPerBase <= 0) {
            $iskPerBase = 150.0;
        }
        if ($shopPerBase <= 0) {
            $shopPerBase = 1.0;
        }

        $shopMajor = ($isk / $iskPerBase) * $shopPerBase;

        return [
            'cents' => (int) round($shopMajor * 100),
            'currency' => $currency,
            'isk' => $isk,
        ];
    }
}
