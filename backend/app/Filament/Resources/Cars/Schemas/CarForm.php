<?php

namespace App\Filament\Resources\Cars\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->relationship('category', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('transmission')
                    ->required()
                    ->default('automatic'),
                TextInput::make('fuel_type')
                    ->required()
                    ->default('petrol'),
                TextInput::make('seats')
                    ->required()
                    ->numeric()
                    ->default(5),
                TextInput::make('bags')
                    ->required()
                    ->numeric()
                    ->default(2),
                TagsInput::make('features')
                    ->placeholder('Add feature (e.g. ac, bluetooth)')
                    ->columnSpanFull(),
                TextInput::make('availability_status')
                    ->required()
                    ->default('available'),
                TextInput::make('base_daily_price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('base_hourly_price')
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('min_rental_hours')
                    ->numeric(),
                TextInput::make('min_rental_days')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('thumbnail_path'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
