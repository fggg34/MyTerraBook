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
}
