<?php

namespace App\Support;

final class AdminMoney
{
    public static function fromCents(int $cents, string $currency = 'ISK'): string
    {
        return strtoupper($currency).' '.self::group((int) intdiv($cents, 100));
    }

    public static function group(int $amount): string
    {
        $sign = $amount < 0 ? '-' : '';
        $digits = (string) abs($amount);
        $out = '';

        while (strlen($digits) > 3) {
            $out = '.'.substr($digits, -3).$out;
            $digits = substr($digits, 0, -3);
        }

        return $sign.$digits.$out;
    }
}
