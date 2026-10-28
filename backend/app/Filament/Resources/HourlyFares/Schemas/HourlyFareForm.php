<?php

namespace App\Filament\Resources\HourlyFares\Schemas;

use App\Support\AdminInputs;
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
                    ->label('Vehicle')
                    ->relationship('car', 'name')
                    ->required()
                    ->searchable(),
                Select::make('price_type_id')
                    ->label('Price type')
                    ->relationship('priceType', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('min_minutes')
                    ->label('From (minutes)')
                    ->required()
                    ->numeric()
                    ->minValue(1),
                TextInput::make('max_minutes')
                    ->label('To (minutes)')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->rule('gte:min_minutes'),
                AdminInputs::isk('total_price_cents', 'Total price')
                    ->required(),
            ]);
    }
}
