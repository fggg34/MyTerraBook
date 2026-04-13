<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Toggle::make('is_enabled')
                    ->default(true)
                    ->required(),
                Toggle::make('auto_confirm_order')
                    ->default(false)
                    ->required(),
                TextInput::make('charge_or_discount')
                    ->required()
                    ->default('none'),
                TextInput::make('charge_discount_type'),
                TextInput::make('charge_fixed_cents')
                    ->numeric(),
                TextInput::make('charge_percent_bips')
                    ->numeric(),
                Textarea::make('config')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
