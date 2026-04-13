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
                    ->required(),
                Select::make('price_type_id')
                    ->relationship('priceType', 'name')
                    ->required(),
                TextInput::make('from_days')
                    ->required()
                    ->numeric(),
                TextInput::make('to_days')
                    ->required()
                    ->numeric(),
                TextInput::make('price_per_day_cents')
                    ->required()
                    ->numeric(),
            ]);
    }
}
