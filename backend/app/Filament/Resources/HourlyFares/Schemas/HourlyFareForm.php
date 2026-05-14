<?php

namespace App\Filament\Resources\HourlyFares\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HourlyFareForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('car_id')
                    ->relationship('car', 'name')
                    ->required()
                    ->searchable(),
                Select::make('price_type_id')
                    ->relationship('priceType', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('min_minutes')
                    ->label('Min minutes')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('max_minutes')
                    ->label('Max minutes')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->rule('gte:min_minutes'),
                TextInput::make('total_price_cents')
                    ->label('Total price (cents)')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ]);
    }
}
