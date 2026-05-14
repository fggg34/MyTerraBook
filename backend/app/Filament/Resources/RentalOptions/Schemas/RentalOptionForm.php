<?php

namespace App\Filament\Resources\RentalOptions\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class RentalOptionForm
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
                                    ->label('Option Name')
                                    ->required(),

                                FileUpload::make('image_path')
                                    ->label('Option Image')
                                    ->disk('public')
                                    ->directory('rental-options')
                                    ->acceptedFileTypes([
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                        'image/gif',
                                        'image/svg+xml',
                                    ]),

                                Toggle::make('resize_image')
                                    ->label('Resize Image')
                                    ->dehydrated(false),

                                TextInput::make('cost_cents')
                                    ->label('Option Price')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('ISK'),

                                Select::make('tax_rate_id')
                                    ->label('Tax Rate')
                                    ->relationship('taxRate', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('No Tax rates Found'),

                                Toggle::make('is_daily_cost')
                                    ->label('Daily Cost')
                                    ->required(),

                                TextInput::make('max_cost_cap_cents')
                                    ->label('Maximum Cost')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->prefix('ISK'),

                                Toggle::make('has_quantity')
                                    ->label('Selectable Quantity')
                                    ->required(),

                                TextInput::make('min_rental_days')
                                    ->label('Minimum days of rent')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->visible(fn (): bool => DatabaseSchema::hasColumn('rental_options', 'min_rental_days'))
                                    ->helperText('Filters the availability of this extra service by number of days of rent.'),

                                TextInput::make('max_rental_days')
                                    ->label('Maximum days of rent')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->visible(fn (): bool => DatabaseSchema::hasColumn('rental_options', 'max_rental_days'))
                                    ->helperText('Filters the availability of this extra service by number of days of rent.'),

                                TextInput::make('sort_order')
                                    ->label('Ordering')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->visible(fn (): bool => DatabaseSchema::hasColumn('rental_options', 'sort_order')),

                                Toggle::make('is_mandatory')
                                    ->label('Always Selected')
                                    ->required(),
                            ]),

                        Section::make('Settings')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                RichEditor::make('description')
                                    ->label('Option Description')
                                    ->columnSpanFull(),

                                Select::make('cars')
                                    ->label('Cars Assigned')
                                    ->relationship('cars', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->columnSpanFull(),

                            ]),
                    ]),
            ]);
    }
}
