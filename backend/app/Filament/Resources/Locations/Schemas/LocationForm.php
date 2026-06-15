<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                    ->extraAttributes(['class' => 'ir-location-form-grid'])
                    ->columnSpanFull()
                    ->schema([

                        // ── LEFT: Details ──────────────────────────────────
                        Section::make('Details')
                            ->extraAttributes(['class' => 'ir-location-form-panel ir-location-form-panel--details'])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 1,
                            ])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Location Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText(
                                        'Locations only appear in homepage search after being assigned to at least one vehicle '
                                        .'(Impact Rent → Cars List → Pickup / Drop Off Locations).'
                                    ),

                                TextInput::make('address')
                                    ->label('Location Address')
                                    ->maxLength(255),

                                TextInput::make('latitude')
                                    ->label('Latitude')
                                    ->numeric()
                                    ->step(0.0000001),

                                TextInput::make('longitude')
                                    ->label('Longitude')
                                    ->numeric()
                                    ->step(0.0000001),

                                Select::make('tax_rate_id')
                                    ->label('Override Tax Rate')
                                    ->relationship('taxRate', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('------')
                                    ->hint('Optional')
                                    ->hintIcon('heroicon-o-question-mark-circle')
                                    ->hintIconTooltip(
                                        'Apply a tax rate override specific to this location. '
                                        .'Leave empty to use the global tax configuration.'
                                    ),

                                Select::make('dropoffCombinations')
                                    ->label('Drop off combinations')
                                    ->multiple()
                                    ->relationship('dropoffCombinations', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->helperText('Optional locations available for drop off when this location is selected for pick up.'),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(5),
                            ]),

                        // ── RIGHT: Settings ────────────────────────────────
                        Section::make('Settings')
                            ->extraAttributes(['class' => 'ir-location-form-panel ir-location-form-panel--settings'])
                            ->columnSpan([
                                'default' => 1,
                                'lg' => 1,
                            ])
                            ->schema([

                                Fieldset::make('Opening Time')
                                    ->hint('Locations are available 24/7. Hosts set pickup and drop-off windows per vehicle.')
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'sm' => 2,
                                        ])
                                            ->schema([
                                                TimePicker::make('default_opening_time')
                                                    ->label('From')
                                                    ->seconds(false)
                                                    ->default('00:00'),

                                                TimePicker::make('default_closing_time')
                                                    ->label('To')
                                                    ->seconds(false)
                                                    ->default('23:59'),
                                            ]),
                                    ]),

                                TimePicker::make('suggested_preselected_time')
                                    ->label('Suggested Time')
                                    ->seconds(false)
                                    ->hint('Optional')
                                    ->hintIcon('heroicon-o-question-mark-circle')
                                    ->hintIconTooltip(
                                        'Pre-select a time in the front-end booking form. '
                                        .'Useful when the location is open 24 h but you want a sensible default shown to the customer (e.g. 09:00).'
                                    ),

                                // Each repeater item stacks Day → Opens at / Closes at
                                Repeater::make('schedules')
                                    ->relationship()
                                    ->label('Override Opening Time')
                                    ->hint('Optional')
                                    ->hintIcon('heroicon-o-question-mark-circle')
                                    ->hintIconTooltip(
                                        'Override the opening / closing time for specific weekdays. '
                                        .'Only days listed here will differ from the global Opening Time above.'
                                    )
                                    ->addActionLabel('Add day override')
                                    ->collapsible()
                                    ->itemLabel(fn (array $state): ?string => match ((int) ($state['weekday'] ?? '')) {
                                        0 => 'Sunday',
                                        1 => 'Monday',
                                        2 => 'Tuesday',
                                        3 => 'Wednesday',
                                        4 => 'Thursday',
                                        5 => 'Friday',
                                        6 => 'Saturday',
                                        default => null,
                                    })
                                    ->schema([
                                        Select::make('weekday')
                                            ->label('Day')
                                            ->options([
                                                1 => 'Monday',
                                                2 => 'Tuesday',
                                                3 => 'Wednesday',
                                                4 => 'Thursday',
                                                5 => 'Friday',
                                                6 => 'Saturday',
                                                0 => 'Sunday',
                                            ])
                                            ->required(),

                                        Grid::make([
                                            'default' => 1,
                                            'sm' => 2,
                                        ])
                                            ->schema([
                                                TimePicker::make('opening_time')
                                                    ->label('Opens at')
                                                    ->seconds(false),

                                                TimePicker::make('closing_time')
                                                    ->label('Closes at')
                                                    ->seconds(false),
                                            ]),
                                    ])
                                    ->columns(1),

                                Repeater::make('closingDays')
                                    ->relationship()
                                    ->label('Closing Days')
                                    ->hint('Optional')
                                    ->hintIcon('heroicon-o-question-mark-circle')
                                    ->hintIconTooltip(
                                        'Define dates or recurring weekdays when this location is closed. '
                                        .'Use "Single Date" for a one-off closure, or choose a weekday for a recurring weekly closure.'
                                    )
                                    ->addActionLabel('Add closing day')
                                    ->collapsible()
                                    ->schema([
                                        Grid::make([
                                            'default' => 1,
                                            'sm' => 2,
                                        ])
                                            ->schema([
                                                Select::make('type')
                                                    ->label('Type')
                                                    ->options([
                                                        'single'    => 'Single Date',
                                                        'recurring' => 'Recurring Weekday',
                                                    ])
                                                    ->default('single')
                                                    ->live()
                                                    ->dehydrated(false),

                                                DatePicker::make('specific_date')
                                                    ->label('Date')
                                                    ->visible(fn ($get) => ($get('type') ?? 'single') === 'single')
                                                    ->native(false),

                                                Select::make('recurring_weekday')
                                                    ->label('Weekday')
                                                    ->options([
                                                        1 => 'Monday',
                                                        2 => 'Tuesday',
                                                        3 => 'Wednesday',
                                                        4 => 'Thursday',
                                                        5 => 'Friday',
                                                        6 => 'Saturday',
                                                        0 => 'Sunday',
                                                    ])
                                                    ->visible(fn ($get) => ($get('type') ?? 'single') === 'recurring'),
                                            ]),
                                    ])
                                    ->columns(1),
                            ]),
                    ]),
            ]);
    }
}
