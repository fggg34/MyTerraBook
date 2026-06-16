<?php

namespace App\Filament\Resources\Characteristics\Schemas;

use App\Support\IconCatalog;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Schema as DatabaseSchema;

class CharacteristicForm
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
                                    ->label('Characteristic Name')
                                    ->required(),

                                Select::make('icon')
                                    ->label('Icon')
                                    ->options(IconCatalog::filamentOptions())
                                    ->searchable()
                                    ->allowHtml()
                                    ->native(false)
                                    ->preload()
                                    ->placeholder('Search and pick an icon')
                                    ->helperText('Pick an icon from the shared library. Used on the public listing page.'),

                                FileUpload::make('icon_path')
                                    ->label('Or upload a custom icon')
                                    ->disk('public')
                                    ->directory('characteristic-icons')
                                    ->image()
                                    ->acceptedFileTypes([
                                        'image/svg+xml',
                                        'image/png',
                                        'image/webp',
                                        'image/jpeg',
                                    ])
                                    ->helperText('Optional. When set, this overrides the library icon above on the public listing and host editor.')
                                    ->visible(fn (): bool => DatabaseSchema::hasColumn('characteristics', 'icon_path')),

                                TextInput::make('display_text')
                                    ->label('Text Next to Icon'),

                                Select::make('group')
                                    ->label('Group')
                                    ->options(array_combine(
                                        \App\Models\Characteristic::GROUPS,
                                        \App\Models\Characteristic::GROUPS,
                                    ))
                                    ->searchable()
                                    ->nullable()
                                    ->visible(fn (): bool => DatabaseSchema::hasColumn('characteristics', 'group'))
                                    ->helperText('Used to organise characteristics into sections for hosts and search filters.'),
                            ]),

                        Section::make('Settings')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                Select::make('cars')
                                    ->label('Cars Assigned')
                                    ->relationship('cars', 'name')
                                    ->multiple()
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('sort_order')
                                    ->label('Ordering position')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn (): bool => DatabaseSchema::hasColumn('characteristics', 'sort_order'))
                                    ->helperText('Leave this field empty for letting the system calculate the ordering position automatically'),

                                Toggle::make('is_search_filter')
                                    ->label('Use as search filter')
                                    ->default(false)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
