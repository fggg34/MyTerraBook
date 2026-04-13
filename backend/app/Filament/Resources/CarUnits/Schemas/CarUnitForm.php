<?php

namespace App\Filament\Resources\CarUnits\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CarUnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('car_id')
                    ->relationship('car', 'name')
                    ->required()
                    ->helperText('The vehicle model this physical unit belongs to. One model can have many fleet units (e.g. each real car).'),
                Toggle::make('is_active')
                    ->default(true)
                    ->required()
                    ->helperText('Inactive units are ignored for assignments and reporting.'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
