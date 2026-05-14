<?php

namespace App\Filament\Resources\TaxRates\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TaxRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Tax Rate Name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('basis_points')
                    ->label('Tax Rate')
                    ->suffix('%')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->formatStateUsing(static fn ($state): ?string => $state === null
                        ? null
                        : number_format(((int) $state) / 100, 2, '.', ''))
                    ->dehydrateStateUsing(static fn ($state): int => (int) round(((float) $state) * 100)),
            ]);
    }
}
