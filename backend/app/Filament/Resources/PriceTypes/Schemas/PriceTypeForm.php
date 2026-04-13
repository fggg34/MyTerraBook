<?php

namespace App\Filament\Resources\PriceTypes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PriceTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        if (filled($state) && blank($get('slug'))) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->helperText('Leave blank to auto-generate from the name when you save.')
                    ->maxLength(255),
                TextInput::make('attribute_label'),
                TextInput::make('attribute_value_per_day'),
                Select::make('tax_rate_id')
                    ->relationship('taxRate', 'name'),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
