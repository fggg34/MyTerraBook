<?php

namespace App\Filament\Resources\Cars\Schemas;

use App\Enums\DriveType;
use App\Models\Characteristic;
use App\Models\Location;
use App\Models\RentalCondition;
use App\Models\RentalOption;
use App\Models\MainCategory;
use App\Models\SubCategory;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;

class CarForm
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
                                    ->label('Name')
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label('Available')
                                    ->default(true)
                                    ->required(),

                                TextInput::make('units_available')
                                    ->label('Total Units')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1),

                                Section::make('Product card specs')
                                    ->description('These four specs appear on search and homepage product cards.')
                                    ->schema([
                                        Select::make('transmission')
                                            ->label('Transmission')
                                            ->options([
                                                'manual' => 'Manual',
                                                'automatic' => 'Automatic',
                                            ])
                                            ->required()
                                            ->native(false),
                                        Select::make('fuel_type')
                                            ->label('Fuel type')
                                            ->options([
                                                'petrol' => 'Petrol',
                                                'diesel' => 'Diesel',
                                                'electric' => 'Electric',
                                                'hybrid' => 'Hybrid',
                                            ])
                                            ->required()
                                            ->native(false),
                                        Select::make('drive_type')
                                            ->label('Drive system')
                                            ->options(collect(DriveType::cases())->mapWithKeys(
                                                fn (DriveType $type) => [$type->value => match ($type) {
                                                    DriveType::Fwd => 'Front-wheel drive (FWD)',
                                                    DriveType::Rwd => 'Rear-wheel drive (RWD)',
                                                    DriveType::Awd => 'All-wheel drive (AWD)',
                                                    DriveType::FourByFour => '4×4',
                                                }]
                                            )->all())
                                            ->required()
                                            ->native(false),
                                        TextInput::make('seats')
                                            ->label('Seats')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->maxValue(50),
                                        TextInput::make('bags')
                                            ->label('Bags')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->maxValue(50),
                                        TextInput::make('sleeps')
                                            ->label('Sleeps (campervans only)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(20)
                                            ->helperText('Required for campervans. Leave 0 for cars.'),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                                FileUpload::make('main_image_path')
                                    ->label('Image')
                                    ->disk('public')
                                    ->directory('cars')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml']),

                                Toggle::make('resize_image')
                                    ->label('Resize image')
                                    ->dehydrated(false),

                                FileUpload::make('details_image_paths')
                                    ->label('Details images')
                                    ->disk('public')
                                    ->directory('cars/details')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                                    ->multiple()
                                    ->reorderable(),

                                Toggle::make('resize_details_image')
                                    ->label('Resize image')
                                    ->dehydrated(false),

                                Select::make('main_category_id')
                                    ->label('Main Category')
                                    ->options(fn (): array => MainCategory::query()
                                        ->orderBy('sort_order')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function ($component, $record): void {
                                        if (! $record?->subCategory) {
                                            return;
                                        }

                                        $component->state((string) $record->subCategory->main_category_id);
                                    })
                                    ->live(),

                                Select::make('sub_category_id')
                                    ->label('Sub Category')
                                    ->options(function (callable $get): array {
                                        $mainCategoryId = $get('main_category_id');

                                        $query = SubCategory::query()->orderBy('name');

                                        if ($mainCategoryId) {
                                            $query->where('main_category_id', $mainCategoryId);
                                        }

                                        return $query->pluck('name', 'id')->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('pickup_location_ids')
                                    ->label('Pickup Locations')
                                    ->multiple()
                                    ->options(fn (): array => Location::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload()
                                    ->afterStateHydrated(function ($component, $record): void {
                                        if (! $record) {
                                            return;
                                        }
                                        $component->state(
                                            $record->locations()
                                                ->wherePivot('allows_pickup', true)
                                                ->pluck('locations.id')
                                                ->map(fn ($id) => (string) $id)
                                                ->all()
                                        );
                                    }),

                                Select::make('dropoff_location_ids')
                                    ->label('Drop Off Locations')
                                    ->multiple()
                                    ->options(fn (): array => Location::query()->orderBy('name')->pluck('name', 'id')->all())
                                    ->searchable()
                                    ->preload()
                                    ->afterStateHydrated(function ($component, $record): void {
                                        if (! $record) {
                                            return;
                                        }
                                        $component->state(
                                            $record->locations()
                                                ->wherePivot('allows_dropoff', true)
                                                ->pluck('locations.id')
                                                ->map(fn ($id) => (string) $id)
                                                ->all()
                                        );
                                    }),

                                Select::make('year')
                                    ->label('Year')
                                    ->options(fn (): array => collect(range(2026, 1990))
                                        ->mapWithKeys(fn (int $year): array => [$year => (string) $year])
                                        ->all())
                                    ->searchable()
                                    ->native(false)
                                    ->placeholder('Select a year'),

                                CheckboxList::make('characteristics')
                                    ->label('Characteristics')
                                    ->relationship(
                                        'characteristics',
                                        'name',
                                        fn ($query) => $query->orderBy('name'),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn (Characteristic $record): string => filled($record->display_text)
                                            ? (string) $record->display_text
                                            : $record->name,
                                    )
                                    ->bulkToggleable()
                                    ->columns(3)
                                    ->gridDirection(GridDirection::Row)
                                    ->columnSpanFull(),

                                CheckboxList::make('rentalOptions')
                                    ->label('Options')
                                    ->relationship(
                                        'rentalOptions',
                                        'name',
                                        fn ($query) => $query->orderBy('name'),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn (RentalOption $record): string => $record->name
                                    )
                                    ->bulkToggleable()
                                    ->columns(3)
                                    ->gridDirection(GridDirection::Row)
                                    ->columnSpanFull(),

                                CheckboxList::make('rentalConditions')
                                    ->label('Rental conditions')
                                    ->relationship(
                                        'rentalConditions',
                                        'title',
                                        fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                                    )
                                    ->getOptionLabelFromRecordUsing(
                                        fn (RentalCondition $record): string => $record->title
                                    )
                                    ->bulkToggleable()
                                    ->columns(2)
                                    ->gridDirection(GridDirection::Row)
                                    ->columnSpanFull(),
                            ]),

                        Grid::make([
                            'default' => 1,
                            'xl' => 1,
                        ])
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                Section::make('Descriptions')
                                    ->schema([
                                        RichEditor::make('description')
                                            ->label('Description')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Parameters')
                                    ->schema([
                                        Toggle::make('show_cost_per_day_in_search_results')
                                            ->label('Show Cost Per Day in Search Results')
                                            ->dehydrated(false),
                                        Toggle::make('enable_request_information')
                                            ->label('Enable Request Information')
                                            ->dehydrated(false),
                                        TextInput::make('custom_label')
                                            ->label('Additional detail features')
                                            ->dehydrated(false),
                                        FileUpload::make('custom_tag_image')
                                            ->label('Custom image / if vacant')
                                            ->disk('public')
                                            ->directory('cars/tags')
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                                            ->dehydrated(false),
                                    ]),
                            ]),

                        Section::make('SEO')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('Meta title')
                                    ->maxLength(255)
                                    ->helperText('Leave empty to auto-generate from the listing name.'),
                                Textarea::make('meta_description')
                                    ->label('Meta description')
                                    ->rows(3)
                                    ->helperText('Leave empty to use the listing description.'),
                                FileUpload::make('og_image')
                                    ->label('Share image (OG)')
                                    ->disk('public')
                                    ->directory('cars/og')
                                    ->image()
                                    ->maxSize(8192)
                                    ->helperText('Leave empty to use the main listing photo.'),
                            ]),

                        Section::make('Import Calendars')
                            ->columnSpan([
                                'default' => 1,
                                'xl' => 6,
                            ])
                            ->schema([
                                TextInput::make('ical_import_url')
                                    ->label('Availability calendar to import')
                                    ->url(),
                            ]),
                    ]),
            ]);
    }
}
