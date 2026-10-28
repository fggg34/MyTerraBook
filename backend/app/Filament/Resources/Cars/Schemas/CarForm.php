<?php

namespace App\Filament\Resources\Cars\Schemas;

use App\Enums\DriveType;
use App\Models\Characteristic;
use App\Models\Location;
use App\Models\MainCategory;
use App\Models\RentalCondition;
use App\Models\RentalOption;
use App\Models\SubCategory;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Enums\GridDirection;

class CarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(self::editSections());
    }

    /**
     * @return list<Step>
     */
    public static function wizardSteps(): array
    {
        return [
            Step::make('Identity')
                ->schema([
                    Section::make('Identity')
                        ->description('What this car is, and where it lives in the fleet.')
                        ->schema(self::identityFields())
                        ->columns(self::pairedColumns()),
                ]),
            Step::make('Specs')
                ->schema([
                    Section::make('Specs')
                        ->description('How the car drives and what it can carry.')
                        ->schema(self::specsFields())
                        ->columns(self::pairedColumns()),
                ]),
            Step::make('Features')
                ->schema([
                    Section::make('Features')
                        ->description('Comfort and equipment tags for this model.')
                        ->schema(self::featuresFields()),
                ]),
            Step::make('Photos')
                ->schema([
                    Section::make('Photos')
                        ->description('Optional. Applied when you create the vehicle.')
                        ->schema(self::photosFields()),
                ]),
            Step::make('Details')
                ->schema(self::detailsFields()),
        ];
    }

    /**
     * @return list<Section>
     */
    public static function editSections(): array
    {
        return [
            Section::make('Identity')
                ->description('What this car is, and where it lives in the fleet.')
                ->schema(self::identityFields())
                ->columns(self::pairedColumns())
                ->extraAttributes(['id' => 'tb-identity'])
                ->columnSpanFull(),
            Section::make('Specs')
                ->description('How the car drives and what it can carry.')
                ->schema(self::specsFields())
                ->columns(self::pairedColumns())
                ->extraAttributes(['id' => 'tb-specs'])
                ->columnSpanFull(),
            Section::make('Features')
                ->description('Comfort and equipment tags for this model.')
                ->schema(self::featuresFields())
                ->extraAttributes(['id' => 'tb-features'])
                ->columnSpanFull(),
            Section::make('Photos')
                ->description('Set a listing cover, then gallery photos.')
                ->schema(self::photosFields())
                ->extraAttributes(['id' => 'tb-photos'])
                ->columnSpanFull(),
            Section::make('Description')
                ->description('Guest-facing copy for this model. Shown on the public listing.')
                ->schema([
                    RichEditor::make('description')
                        ->label('Description')
                        ->columnSpanFull(),
                ])
                ->extraAttributes(['id' => 'tb-description'])
                ->columnSpanFull(),
            Section::make('SEO')
                ->description('Search and social sharing for the public vehicle page.')
                ->schema(self::seoFields())
                ->columns(1)
                ->extraAttributes(['id' => 'tb-seo'])
                ->columnSpanFull(),
            Section::make('Notes & extras')
                ->description('Internal only. Not shown to guests.')
                ->schema(self::notesFields())
                ->columns(self::pairedColumns())
                ->extraAttributes(['id' => 'tb-notes'])
                ->columnSpanFull(),
        ];
    }

    /**
     * Two columns from the small breakpoint, matching the Greenlight vehicle cards.
     *
     * @return array<string, int>
     */
    private static function pairedColumns(): array
    {
        return [
            'default' => 1,
            'sm' => 2,
        ];
    }

    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    private static function identityFields(): array
    {
        return [
            TextInput::make('name')
                ->label('Vehicle name')
                ->placeholder('Toyota Rav4')
                ->helperText('Make and model as guests should see it.')
                ->required(),
            Select::make('year')
                ->label('Year')
                ->options(fn (): array => collect(range(2026, 1990))
                    ->mapWithKeys(fn (int $year): array => [$year => (string) $year])
                    ->all())
                ->searchable()
                ->native(false)
                ->placeholder('Select a year'),
            Select::make('main_category_id')
                ->label('Main category')
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
                ->label('Category')
                ->helperText('The first category guests see on the listing.')
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
                ->label('Pickup locations')
                ->helperText('Where guests can pick up this car.')
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
                ->label('Drop-off locations')
                ->helperText('Where guests can return this car.')
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
            TextInput::make('units_available')
                ->label('Total units')
                ->belowLabel('How many matching cars of this model you have.')
                ->required()
                ->numeric()
                ->minValue(1)
                ->default(1)
                ->extraFieldWrapperAttributes(['class' => 'ir-units-field'])
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    private static function specsFields(): array
    {
        return [
            Placeholder::make('base_price_hint')
                ->label('Base price per day')
                ->content('Set the daily rate under Pricing, Base prices. Create starts without a fare.')
                ->extraEntryWrapperAttributes(['class' => 'ir-price-field']),
            Toggle::make('is_active')
                ->label('Available')
                ->belowLabel('Off hides the car from booking.')
                ->default(true)
                ->required()
                ->inline(false)
                ->extraFieldWrapperAttributes(['class' => 'ir-available-field'])
                ->columnSpanFull(),
            Select::make('drive_type')
                ->label('Drivetrain')
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
            Select::make('transmission')
                ->label('Transmission')
                ->options([
                    'manual' => 'Manual',
                    'automatic' => 'Automatic',
                ])
                ->required()
                ->native(false),
            Select::make('fuel_type')
                ->label('Fuel')
                ->options([
                    'petrol' => 'Petrol',
                    'diesel' => 'Diesel',
                    'electric' => 'Electric',
                    'hybrid' => 'Hybrid',
                ])
                ->required()
                ->native(false),
            TextInput::make('seats')
                ->label('Seats')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(50)
                ->default(5),
            TextInput::make('sleeps')
                ->label('Sleeping spots')
                ->helperText('Beds / sleeping capacity. Use 0 if none.')
                ->numeric()
                ->minValue(0)
                ->maxValue(20)
                ->default(0),
            TextInput::make('bags')
                ->label('Luggage capacity')
                ->helperText('Soft bags / suitcases.')
                ->numeric()
                ->required()
                ->minValue(1)
                ->maxValue(50)
                ->default(2),
        ];
    }

    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    private static function featuresFields(): array
    {
        return [
            CheckboxList::make('characteristics')
                ->label('Features')
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
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                ->gridDirection(GridDirection::Row)
                ->columnSpanFull(),
            CheckboxList::make('rentalOptions')
                ->label('Add-ons')
                ->relationship(
                    'rentalOptions',
                    'name',
                    fn ($query) => $query->orderBy('name'),
                )
                ->getOptionLabelFromRecordUsing(
                    fn (RentalOption $record): string => $record->name
                )
                ->bulkToggleable()
                ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
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
                ->columns(self::pairedColumns())
                ->gridDirection(GridDirection::Row)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    private static function photosFields(): array
    {
        return [
            FileUpload::make('main_image_path')
                ->label('Listing main image')
                ->helperText('Cover photo shown on listings and vehicle cards. One image only.')
                ->disk('public')
                ->directory('cars')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->image()
                ->columnSpanFull(),
            Toggle::make('resize_image')
                ->label('Resize listing image')
                ->dehydrated(false),
            FileUpload::make('details_image_paths')
                ->label('Gallery images')
                ->helperText('Extra photos for the detail page gallery. You can select multiple.')
                ->disk('public')
                ->directory('cars/details')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->image()
                ->multiple()
                ->reorderable()
                ->columnSpanFull(),
            Toggle::make('resize_details_image')
                ->label('Resize gallery images')
                ->dehydrated(false),
        ];
    }

    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    private static function detailsFields(): array
    {
        return [
            Section::make('Description')
                ->description('Guest-facing copy for this model. Shown on the public listing.')
                ->schema([
                    RichEditor::make('description')
                        ->label('Description')
                        ->placeholder('Describe the car, what makes it a good fit, included kit.')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
            Section::make('SEO')
                ->description('Search and social sharing for the public vehicle page.')
                ->schema(self::seoFields())
                ->columnSpanFull(),
            Section::make('Notes and extras')
                ->description('Internal only. Not shown to guests.')
                ->schema(self::notesFields())
                ->columns(self::pairedColumns())
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    private static function seoFields(): array
    {
        return [
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
        ];
    }

    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    private static function notesFields(): array
    {
        return [
            TextInput::make('ical_import_url')
                ->label('Availability calendar to import')
                ->url()
                ->columnSpanFull(),
            Toggle::make('show_cost_per_day_in_search_results')
                ->label('Show cost per day in search results')
                ->dehydrated(false),
            Toggle::make('enable_request_information')
                ->label('Enable request information')
                ->dehydrated(false),
            TextInput::make('custom_label')
                ->label('Additional detail features')
                ->dehydrated(false)
                ->columnSpanFull(),
            FileUpload::make('custom_tag_image')
                ->label('Custom image / if vacant')
                ->disk('public')
                ->directory('cars/tags')
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->dehydrated(false)
                ->columnSpanFull(),
            View::make('filament.resources.cars.components.delete-vehicle')
                ->columnSpanFull()
                ->visible(fn ($livewire): bool => is_object($livewire) && method_exists($livewire, 'getRecord') && filled($livewire->getRecord()?->getKey())),
        ];
    }
}
