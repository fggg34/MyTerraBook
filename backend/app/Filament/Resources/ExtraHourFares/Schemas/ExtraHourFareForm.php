<?php

namespace App\Filament\Resources\ExtraHourFares\Schemas;

use App\Support\AdminInputs;
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
                    ->label('Vehicle')
                    ->relationship('car', 'name')
                    ->required()
                    ->searchable(),
                Select::make('price_type_id')
                    ->label('Price type')
                    ->relationship('priceType', 'name')
                    ->required()
                    ->searchable(),
                AdminInputs::isk('charge_per_extra_hour_cents', 'Charge per extra hour')
                    ->required(),
            ]);
    }
}
