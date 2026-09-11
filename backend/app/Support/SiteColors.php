<?php

namespace App\Support;

class SiteColors
{
    /** @var array<string, string> */
    public const CSS_VARIABLES = [
        'navy' => '--navy',
        'ink' => '--ink',
        'slate' => '--slate',
        'slateLight' => '--slate-light',
        'green' => '--green',
        'greenDark' => '--green-dark',
        'blue' => '--blue',
        'blueSoft' => '--blue-soft',
        'line' => '--line',
        'bg' => '--bg',
        'red' => '--red',
        'accent' => '--accent',
    ];

    /**
     * @param  array<string, mixed>  $colors
     */
    public static function toRootCss(array $colors): string
    {
        $rules = [];

        foreach (self::CSS_VARIABLES as $key => $variable) {
            $hex = self::normalizeHex($colors[$key] ?? null);
            if ($hex === null) {
                continue;
            }

            $rules[] = $variable.': '.$hex.' !important';
        }

        if ($rules === []) {
            return '';
        }

        return ':root{'.implode(';', $rules).';}';
    }

    public static function normalizeHex(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^#?([0-9a-fA-F]{3})$/', $value, $matches) === 1) {
            $hex = strtolower($matches[1]);

            return '#'.$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (preg_match('/^#?([0-9a-fA-F]{6})$/', $value, $matches) === 1) {
            return '#'.strtolower($matches[1]);
        }

        return null;
    }
}
