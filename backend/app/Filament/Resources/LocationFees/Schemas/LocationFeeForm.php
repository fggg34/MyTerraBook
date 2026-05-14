<?php

namespace App\Filament\Resources\LocationFees\Schemas;

use App\Models\Setting;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationFeeForm
{
    public static function configure(Schema $schema): Schema
    {
        $currencyCode = (string) data_get(
            Setting::getValue('shop.currency', ['code' => 'EUR']),
            'code',
            'EUR'
        );

        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'xl' => 12,
                ])
                    ->columnSpanFull()
                    ->schema([
                        Section::make('Details')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                Toggle::make('is_one_way_fee')
                                    ->label('One-way Fee')
                                    ->helperText('One-way fees include any possible combination of pickup location different from the drop-off location.')
                                    ->default(false)
                                    ->inline(false)
                                    ->required(),

                                Select::make('pickup_location_id')
                                    ->label('Pickup Location')
                                    ->relationship('pickupLocation', 'name')
                                    ->required()
                                    ->searchable(),

                                Select::make('dropoff_location_id')
                                    ->label('Drop Off Location')
                                    ->relationship('dropoffLocation', 'name')
                                    ->required()
                                    ->searchable(),

                                Toggle::make('apply_inverted')
                                    ->label('Apply if the Locations are inverted')
                                    ->default(false)
                                    ->required(),

                                TextInput::make('cost_cents')
                                    ->label('Cost')
                                    ->prefix($currencyCode)
                                    ->required()
                                    ->numeric()
                                    ->minValue(0),

                                Toggle::make('multiply_by_days')
                                    ->label('Daily Cost')
                                    ->helperText('When enabled, cost is multiplied by rental days.')
                                    ->default(false)
                                    ->required(),
                            ]),

                        Section::make('Settings')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                Select::make('tax_rate_id')
                                    ->label('Tax Rate')
                                    ->relationship('taxRate', 'name')
                                    ->searchable(),

                                Placeholder::make('tax_rate_hint')
                                    ->hiddenLabel()
                                    ->content('Tax rates can be managed in Rental > Tax Rates.'),

                                KeyValue::make('day_overrides')
                                    ->label('Cost Overrides')
                                    ->helperText('Override fee by rental day count, for example 3 => 1500.')
                                    ->keyLabel('Rental days')
                                    ->valueLabel('Cost')
                                    ->columnSpanFull(),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
