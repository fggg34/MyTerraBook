<?php

namespace App\Filament\Resources\Coupons\Schemas;

use App\Models\Car;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('type')
                    ->required(),
                TextInput::make('discount_type')
                    ->required(),
                TextInput::make('discount_fixed_cents')
                    ->numeric(),
                TextInput::make('discount_percent_bips')
                    ->numeric(),
                Select::make('vehicle_ids')
                    ->label('Limit to vehicles')
                    ->multiple()
                    ->options(fn () => Car::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                DatePicker::make('valid_from'),
                DatePicker::make('valid_to'),
                TextInput::make('min_order_total_cents')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
