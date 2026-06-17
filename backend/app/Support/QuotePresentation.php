<?php

namespace App\Support;

class QuotePresentation
{
    /**
     * @param  list<array{label: string, kind?: string, amount_cents: int}>  $feesLines
     * @return list<array{label: string, kind: string, amount: string}>
     */
    public static function feesLines(array $feesLines): array
    {
        return array_map(
            fn (array $line) => [
                'label' => $line['label'],
                'kind' => $line['kind'] ?? 'fee',
                'amount' => Money::formatDecimalFromCents($line['amount_cents']),
            ],
            $feesLines,
        );
    }

    /**
     * @param  list<array{rental_option_id: int, name: string, quantity: int, total_cents: int}>  $extrasLines
     * @return list<array{rental_option_id: int, name: string, quantity: int, amount: string}>
     */
    public static function extrasLines(array $extrasLines): array
    {
        return array_map(
            fn (array $line) => [
                'rental_option_id' => $line['rental_option_id'],
                'name' => $line['name'],
                'quantity' => $line['quantity'],
                'amount' => Money::formatDecimalFromCents($line['total_cents']),
            ],
            $extrasLines,
        );
    }
}
