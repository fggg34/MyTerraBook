<?php

namespace App\Filament\Resources\RentalConditions\Schemas;

use App\Support\IconCatalog;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RentalConditionForm
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
                                    ->label('Admin name')
                                    ->required()
                                    ->maxLength(120)
                                    ->helperText('Internal label used in the admin panel and host editor search.'),

                                TextInput::make('title')
                                    ->label('Listing title')
                                    ->required()
                                    ->maxLength(160)
                                    ->helperText('Short heading shown on the public Rental conditions tab.'),

                                Textarea::make('description')
                                    ->label('Listing description')
                                    ->required()
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->helperText('Supporting text shown under the title on the listing page.'),

                                Select::make('icon')
                                    ->label('Icon')
                                    ->options(IconCatalog::filamentOptions())
                                    ->searchable()
                                    ->allowHtml()
                                    ->native(false)
                                    ->preload()
                                    ->placeholder('Search and pick an icon')
                                    ->helperText('Optional. Shown in the host editor when selecting conditions.'),
                            ]),

                        Section::make('Settings')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                Select::make('cars')
                                    ->label('Cars assigned')
                                    ->relationship('cars', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('sort_order')
                                    ->label('Ordering position')
                                    ->numeric()
                                    ->minValue(0)
                                    ->helperText('Leave empty to append at the end of the list.'),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
