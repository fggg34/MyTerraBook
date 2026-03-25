<?php

namespace App\Filament\Resources\PricingRules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PricingRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('rule_kind')
                    ->required(),
                Select::make('car_id')
                    ->relationship('car', 'name'),
                Select::make('location_id')
                    ->relationship('location', 'name'),
                DatePicker::make('date_from'),
                DatePicker::make('date_to'),
                TextInput::make('time_unit')
                    ->required()
                    ->default('day'),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('adjustment')
                    ->required()
                    ->default('set'),
                TextInput::make('priority')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('min_duration_days')
                    ->numeric(),
                TextInput::make('min_duration_hours')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
