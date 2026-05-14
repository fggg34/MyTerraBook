<?php

namespace App\Filament\Resources\OutOfHoursFees\Schemas;

use App\Models\Car;
use App\Models\Location;
use App\Models\Setting;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OutOfHoursFeeForm
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
                                TextInput::make('name')
                                    ->label('Name')
                                    ->maxLength(120),

                                TimePicker::make('time_from')
                                    ->label('From Time')
                                    ->required()
                                    ->seconds(false),

                                TimePicker::make('time_to')
                                    ->label('To Time')
                                    ->required()
                                    ->seconds(false),

                                TextInput::make('pickup_cost_cents')
                                    ->label('Pick Up Charge')
                                    ->prefix($currencyCode)
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->default(0)
                                    ->formatStateUsing(fn ($state, $record): int => (int) ($state ?? $record?->cost_cents ?? 0)),

                                TextInput::make('dropoff_cost_cents')
                                    ->label('Drop Off Charge')
                                    ->prefix($currencyCode)
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->default(0)
                                    ->formatStateUsing(fn ($state, $record): int => (int) ($state ?? $record?->cost_cents ?? 0)),

                                TextInput::make('cost_cents')
                                    ->hidden()
                                    ->default(0)
                                    ->dehydrated(true),

                                TextInput::make('max_combined_charge_cents')
                                    ->label('Max Charge')
                                    ->prefix($currencyCode)
                                    ->numeric()
                                    ->minValue(0),

                                Select::make('applies_to')
                                    ->label('Pick up/Drop Off')
                                    ->options([
                                        'pickup' => 'Pick up only',
                                        'dropoff' => 'Drop off only',
                                        'both' => 'Both',
                                    ])
                                    ->default('pickup')
                                    ->native(false)
                                    ->required(),

                                Select::make('tax_rate_id')
                                    ->label('Tax Rate')
                                    ->relationship('taxRate', 'name')
                                    ->searchable(),

                                Placeholder::make('tax_rate_hint')
                                    ->hiddenLabel()
                                    ->content('Tax rates can be managed in Rental > Tax Rates.'),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->required(),
                            ]),

                        Section::make('Settings')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                CheckboxList::make('weekday_filter')
                                    ->label('Week Days')
                                    ->options(self::weekdayOptions())
                                    ->columns(4)
                                    ->helperText('Leave empty to allow all weekdays.')
                                    ->columnSpanFull(),

                                Select::make('vehicle_ids')
                                    ->label('Cars')
                                    ->multiple()
                                    ->options(fn (): array => Car::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->helperText('Leave empty to apply to all cars.')
                                    ->columnSpanFull(),

                                Select::make('location_ids')
                                    ->label('Locations')
                                    ->multiple()
                                    ->options(fn (): array => Location::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->helperText('Leave empty to apply to all locations.')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    private static function weekdayOptions(): array
    {
        return [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];
    }
}
