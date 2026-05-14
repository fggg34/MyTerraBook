<?php

namespace App\Filament\Resources\SpecialPrices\Schemas;

use App\Models\Car;
use App\Models\Location;
use App\Models\PriceType;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpecialPriceForm
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
                        Section::make('Seasons and Week Days')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                ])
                                    ->schema([
                                        DatePicker::make('date_from')
                                            ->label('From Date'),
                                        DatePicker::make('date_to')
                                            ->label('To Date')
                                            ->afterOrEqual('date_from'),
                                    ]),

                                CheckboxList::make('weekdays')
                                    ->label('Week Days')
                                    ->options(self::weekdayOptions())
                                    ->columns(4)
                                    ->helperText('Selecting no weekdays equals selecting all seven weekdays.')
                                    ->columnSpanFull(),

                                TextInput::make('name')
                                    ->label('Special Price Name')
                                    ->required()
                                    ->maxLength(120),

                                Toggle::make('year_enabled')
                                    ->label('Tied to the Year')
                                    ->dehydrated(false)
                                    ->default(false)
                                    ->formatStateUsing(fn ($record): bool => filled($record?->year)),

                                TextInput::make('year')
                                    ->numeric()
                                    ->minValue(2000)
                                    ->maxValue(2100)
                                    ->visible(fn ($get): bool => (bool) $get('year_enabled')),

                                Toggle::make('apply_after_season_start')
                                    ->label('Pickup Date must be after beginning of the Season')
                                    ->default(false),

                                Toggle::make('lock_first_day_rate')
                                    ->label('Keep First Day Rate')
                                    ->default(false),
                            ]),

                        Section::make('Pricing Modifications')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                Select::make('type')
                                    ->label('Type')
                                    ->options([
                                        'charge' => 'Charge',
                                        'discount' => 'Discount',
                                    ])
                                    ->default('charge')
                                    ->native(false)
                                    ->required(),

                                Select::make('value_mode')
                                    ->label('Value Type')
                                    ->options([
                                        'percentage' => 'Percentage',
                                        'fixed' => 'Fixed',
                                    ])
                                    ->default('percentage')
                                    ->native(false)
                                    ->required(),

                                TextInput::make('value_percent_bips')
                                    ->label('Value (percentage basis points)')
                                    ->helperText('100 = 1%, 1000 = 10%')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn ($get): bool => $get('value_mode') === 'percentage'),

                                TextInput::make('value_fixed_cents')
                                    ->label('Value (fixed cents)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn ($get): bool => $get('value_mode') === 'fixed'),

                                KeyValue::make('day_overrides')
                                    ->label('Value Overrides')
                                    ->helperText('Override by rental day count, e.g. 3 => 1200 (cents).')
                                    ->keyLabel('Rental Days')
                                    ->valueLabel('Value (cents)')
                                    ->columnSpanFull(),

                                Select::make('round_to_integer')
                                    ->label('Round to Integer')
                                    ->options([
                                        0 => '- disabled -',
                                        1 => 'Enabled',
                                    ])
                                    ->default(0)
                                    ->native(false)
                                    ->required(),

                                Select::make('vehicle_ids')
                                    ->label('Cars')
                                    ->multiple()
                                    ->options(fn (): array => Car::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->helperText('Leave empty to apply to all cars.')
                                    ->columnSpanFull(),

                                Select::make('price_type_ids')
                                    ->label('Types of Price')
                                    ->multiple()
                                    ->options(fn (): array => PriceType::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->helperText('Leave empty to apply to any price type.')
                                    ->columnSpanFull(),

                                Select::make('pickup_location_ids')
                                    ->label('Pickup Locations')
                                    ->multiple()
                                    ->options(fn (): array => Location::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->helperText('Leave empty for any pickup location.')
                                    ->columnSpanFull(),

                                Select::make('dropoff_location_ids')
                                    ->label('Drop-off Locations')
                                    ->multiple()
                                    ->options(fn (): array => Location::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->helperText('Leave empty for any drop-off location.')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Promotion')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 12,
                            ])
                            ->schema([
                                Toggle::make('is_promotion')
                                    ->label('Promotion')
                                    ->default(false),
                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->required(),
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
