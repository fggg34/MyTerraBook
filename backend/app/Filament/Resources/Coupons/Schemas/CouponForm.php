<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DateTimePicker;
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
                TextInput::make('discount_type')
                    ->required(),
                TextInput::make('discount_value')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('expires_at'),
                TextInput::make('usage_limit')
                    ->numeric(),
                TextInput::make('times_used')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('min_order_amount')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
