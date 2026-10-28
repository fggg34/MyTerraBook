<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use App\Support\AdminInputs;
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
                    ->label('Code')
                    ->required(),
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                Toggle::make('is_enabled')
                    ->label('Enabled')
                    ->default(true)
                    ->required(),
                Toggle::make('auto_confirm_order')
                    ->label('Confirm the order automatically')
                    ->default(false)
                    ->required(),
                TextInput::make('charge_or_discount')
                    ->label('Charge or discount')
                    ->required()
                    ->default('none'),
                TextInput::make('charge_discount_type')
                    ->label('Amount type'),
                AdminInputs::isk('charge_fixed_cents', 'Fixed amount'),
                AdminInputs::percentFromBips('charge_percent_bips', 'Percent'),
                Textarea::make('config')
                    ->label('Configuration')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->label('Sort order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
