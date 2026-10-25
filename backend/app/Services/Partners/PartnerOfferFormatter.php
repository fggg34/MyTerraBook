<?php

namespace App\Services\Partners;

use App\Support\Money;

final class PartnerOfferFormatter
{
    /**
     * @return array{id: string, name: string, description: string, cost: string, cost_cents: int, per_day: bool, is_daily_cost: bool, max_quantity: int|null, currency: string}
     */
    public static function offer(string $id, string $name, string $description, int $costCents, bool $perDay, string $currency = 'ISK', ?int $maxQuantity = null): array
    {
        return [
            'id' => $id,
            'name' => $name !== '' ? $name : 'Option',
            'description' => self::plain($description),
            'cost' => Money::formatDecimalFromCents($costCents),
            'cost_cents' => max(0, $costCents),
            'per_day' => $perDay,
            'is_daily_cost' => $perDay,
            'max_quantity' => $maxQuantity,
            'currency' => $currency,
        ];
    }

    /**
     * @return array{name: string}|null
     */
    public static function characteristic(string $name): ?array
    {
        $name = self::plain($name);

        return $name === '' ? null : ['name' => $name];
    }

    public static function plain(string $value): string
    {
        $text = trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';
        if (mb_strlen($text) > 420) {
            $text = rtrim(mb_substr($text, 0, 417)).'...';
        }

        return $text;
    }
}
