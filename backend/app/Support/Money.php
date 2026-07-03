<?php

namespace App\Support;

final class Money
{
    public static function formatDecimalFromCents(int $cents, int $decimals = 2): string
    {
        $negative = $cents < 0;
        $abs = $negative ? -$cents : $cents;
        $major = intdiv($abs, 100);
        $minor = $abs % 100;
        $sign = $negative ? '-' : '';

        return $sign.sprintf('%d.%0'.$decimals.'d', $major, $minor);
    }

    /**
     * Format an amount in Icelandic króna (zero-decimal currency).
     */
    public static function formatIsk(float|int $amount): string
    {
        return 'ISK '.number_format((int) round((float) $amount), 0, '.', ',');
    }

    /**
     * Format stored "cents" value as whole ISK (major units).
     */
    public static function formatIskFromCents(int $cents): string
    {
        return self::formatIsk($cents / 100);
    }
}
