<?php

namespace App\Filament\Resources\LocationFees\Schemas;

use App\Support\AdminInputs;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationFeeForm
{
    public static function configure(Schema $schema): Schema
    {
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

                                AdminInputs::isk('cost_cents', 'Fee')
                                    ->required(),

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
                                    ->label('Fee overrides')
                                    ->helperText('The key is the number of days and the value is the fee in ISK.')
                                    ->keyLabel('Rental days')
                                    ->valueLabel('Fee (ISK)')
                                    ->afterStateHydrated(function (KeyValue $component, mixed $state): void {
                                        if (! is_array($state)) {
                                            return;
                                        }
                                        $component->state(collect($state)->map(function (mixed $value): mixed {
                                            if ($value === null || $value === '') {
                                                return $value;
                                            }

                                            return (string) intdiv((int) $value, 100);
                                        })->all());
                                    })
                                    ->dehydrateStateUsing(function (mixed $state): mixed {
                                        if (! is_array($state)) {
                                            return $state;
                                        }

                                        return collect($state)->map(function (mixed $value): mixed {
                                            if ($value === null || $value === '') {
                                                return $value;
                                            }

                                            return (int) round(((float) $value) * 100);
                                        })->all();
                                    })
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
