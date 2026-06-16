<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Validation\Rule;

class PricingCurrency
{
    /**
     * @return list<string>
     */
    public static function supported(): array
    {
        return config('currencies.supported', ['EUR', 'USD', 'GBP', 'ISK']);
    }

    public static function validationRule(): \Illuminate\Validation\Rules\In
    {
        return Rule::in(self::supported());
    }

    public static function shopDefault(): string
    {
        $code = strtoupper((string) data_get(
            Setting::getValue('shop.currency', ['code' => 'EUR']),
            'code',
            'EUR',
        ));

        return in_array($code, self::supported(), true) ? $code : 'EUR';
    }

    public static function forUser(?User $user): string
    {
        if ($user !== null) {
            $code = strtoupper((string) ($user->currency ?? ''));

            if ($code !== '' && in_array($code, self::supported(), true)) {
                return $code;
            }
        }

        return self::shopDefault();
    }

    public static function normalize(?string $code): ?string
    {
        if ($code === null || trim($code) === '') {
            return null;
        }

        $normalized = strtoupper(trim($code));

        return in_array($normalized, self::supported(), true) ? $normalized : null;
    }
}
