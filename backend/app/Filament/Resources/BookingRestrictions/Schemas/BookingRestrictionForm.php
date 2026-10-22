<?php

namespace App\Filament\Resources\BookingRestrictions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingRestrictionForm
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
                            TextInput::make('name')
                                ->label('Restriction Name')
                                ->required()
                                ->maxLength(120),

                            Select::make('restriction_period_mode')
                                ->label('Dates Range/Month')
                                ->options([
                                    'date_range' => 'Dates Range',
                                ])
                                ->default('date_range')
                                ->dehydrated(false)
                                ->native(false)
                                ->required(),

                            Fieldset::make('Dates Range')
                                ->schema([
                                    Grid::make([
                                        'default' => 1,
                                        'sm' => 2,
                                    ])->schema([
                                        DatePicker::make('date_from')
                                            ->label('From Date')
                                            ->required(),
                                        DatePicker::make('date_to')
                                            ->label('To Date')
                                            ->required()
                                            ->afterOrEqual('date_from'),
                                    ]),
                                ]),

                            Toggle::make('apply_to_all_cars')
                                ->label('Apply to all Cars')
                                ->default(true)
                                ->dehydrated(false),
                        ]),

                    Section::make('Settings')
                        ->columnSpan([
                            'default' => 1,
                            'xl' => 6,
                        ])
                        ->schema([
                            TextInput::make('min_rental_days')
                                ->label('Min Num of Days')
                                ->numeric()
                                ->minValue(1),

                            Toggle::make('min_length_multiplier')
                                ->label('Multiply Min Num of Days')
                                ->helperText('When enabled, minimum days multiplier is applied.')
                                ->inline(false),

                            TextInput::make('max_rental_days')
                                ->label('Max Num of Days')
                                ->numeric()
                                ->minValue(1)
                                ->rule('gte:min_rental_days'),

                            Toggle::make('cta_enabled')
                                ->label('Set Days Closed to Arrival (CTA)')
                                ->default(false)
                                ->dehydrated()
                                ->formatStateUsing(fn ($record): bool => ! empty($record?->cta_weekdays)),

                            Toggle::make('ctd_enabled')
                                ->label('Set Days Closed to Departure (CTD)')
                                ->default(false)
                                ->dehydrated()
                                ->formatStateUsing(fn ($record): bool => ! empty($record?->ctd_weekdays)),

                            Select::make('forced_pickup_weekday')
                                ->label('Force Arrival Week Day')
                                ->options(self::weekdayOptions())
                                ->dehydrated()
                                ->native(false)
                                ->formatStateUsing(function ($record): ?string {
                                    $weekday = $record?->forced_pickup_weekdays[0] ?? null;

                                    return $weekday === null || $weekday === '' ? null : (string) $weekday;
                                }),

                            Toggle::make('is_active')
                                ->label('Active')
                                ->default(true)
                                ->required(),
                        ]),
                    ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function persistWeekdayControls(array $data): array
    {
        $allWeekdays = [0, 1, 2, 3, 4, 5, 6];

        $data['cta_weekdays'] = ($data['cta_enabled'] ?? false) ? $allWeekdays : [];
        $data['ctd_weekdays'] = ($data['ctd_enabled'] ?? false) ? $allWeekdays : [];

        $forcedWeekday = $data['forced_pickup_weekday'] ?? null;
        $data['forced_pickup_weekdays'] = ($forcedWeekday === null || $forcedWeekday === '')
            ? []
            : [(int) $forcedWeekday];

        unset(
            $data['cta_enabled'],
            $data['ctd_enabled'],
            $data['forced_pickup_weekday'],
            $data['restriction_period_mode'],
            $data['apply_to_all_cars'],
        );

        return $data;
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
