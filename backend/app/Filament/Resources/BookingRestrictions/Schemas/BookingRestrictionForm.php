<?php

namespace App\Filament\Resources\BookingRestrictions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BookingRestrictionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                DatePicker::make('date_from')
                    ->required(),
                DatePicker::make('date_to')
                    ->required(),
                TextInput::make('min_rental_days')
                    ->numeric(),
                TextInput::make('max_rental_days')
                    ->numeric(),
                Textarea::make('cta_weekdays')
                    ->columnSpanFull(),
                Textarea::make('ctd_weekdays')
                    ->columnSpanFull(),
                Textarea::make('forced_pickup_weekdays')
                    ->columnSpanFull(),
                TextInput::make('min_length_multiplier')
                    ->numeric(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
