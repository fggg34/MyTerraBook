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
                    ->required(),
                Select::make('price_type_id')
                    ->relationship('priceType', 'name')
                    ->required(),
                TextInput::make('min_minutes')
                    ->required()
                    ->numeric(),
                TextInput::make('max_minutes')
                    ->required()
                    ->numeric(),
                TextInput::make('total_price_cents')
                    ->required()
                    ->numeric(),
            ]);
    }
}
