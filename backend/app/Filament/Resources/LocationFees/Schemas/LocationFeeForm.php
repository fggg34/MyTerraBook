<?php

namespace App\Filament\Resources\LocationFees\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LocationFeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pickup_location_id')
                    ->relationship('pickupLocation', 'name')
                    ->required(),
                Select::make('dropoff_location_id')
                    ->relationship('dropoffLocation', 'name')
                    ->required(),
                TextInput::make('cost_cents')
                    ->required()
                    ->numeric(),
                Toggle::make('multiply_by_days')
                    ->required(),
                Select::make('tax_rate_id')
                    ->relationship('taxRate', 'name'),
                Toggle::make('apply_inverted')
                    ->required(),
                Textarea::make('day_overrides')
                    ->columnSpanFull(),
                Toggle::make('is_one_way_fee')
                    ->required(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
