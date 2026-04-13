<?php

namespace App\Filament\Resources\ExtraHourFares\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExtraHourFareForm
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
                TextInput::make('charge_per_extra_hour_cents')
                    ->required()
                    ->numeric(),
            ]);
    }
}
