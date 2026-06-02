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
}
