<?php

namespace App\Filament\Resources\DailyFares\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DailyFareForm
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
                TextInput::make('from_days')
                    ->label('From days')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('to_days')
                    ->label('To days')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->rule('gte:from_days'),
                TextInput::make('price_per_day_cents')
                    ->label('Price per day (cents)')
                    ->required()
                    ->numeric()
                    ->minValue(0),
            ]);
    }
}
