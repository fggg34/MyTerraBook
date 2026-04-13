<?php

namespace App\Filament\Resources\OutOfHoursFees\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OutOfHoursFeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TimePicker::make('time_from')
                    ->required(),
                TimePicker::make('time_to')
                    ->required(),
                TextInput::make('applies_to')
                    ->required(),
                TextInput::make('cost_cents')
                    ->required()
                    ->numeric(),
                TextInput::make('max_combined_charge_cents')
                    ->numeric(),
                Textarea::make('vehicle_ids')
                    ->columnSpanFull(),
                Textarea::make('location_ids')
                    ->columnSpanFull(),
                Textarea::make('weekday_filter')
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
