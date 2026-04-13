<?php

namespace App\Filament\Resources\PriceTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PriceTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('attribute_label'),
                TextInput::make('attribute_value_per_day'),
                Select::make('tax_rate_id')
                    ->relationship('taxRate', 'name'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
