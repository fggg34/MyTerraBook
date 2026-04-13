<?php

namespace App\Filament\Resources\SpecialPrices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SpecialPriceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                DatePicker::make('date_from'),
                DatePicker::make('date_to'),
                Textarea::make('weekdays')
                    ->columnSpanFull(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('value_mode')
                    ->required(),
                TextInput::make('value_fixed_cents')
                    ->numeric(),
                TextInput::make('value_percent_bips')
                    ->numeric(),
                Textarea::make('day_overrides')
                    ->columnSpanFull(),
                Textarea::make('vehicle_ids')
                    ->columnSpanFull(),
                Textarea::make('pickup_location_ids')
                    ->columnSpanFull(),
                Textarea::make('dropoff_location_ids')
                    ->columnSpanFull(),
                Toggle::make('apply_after_season_start')
                    ->required(),
                Toggle::make('lock_first_day_rate')
                    ->required(),
                Toggle::make('round_to_integer')
                    ->required(),
                TextInput::make('year')
                    ->numeric(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
