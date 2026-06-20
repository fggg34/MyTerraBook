<?php

namespace App\Filament\GuestHouse\Resources\Schemas;

use App\Enums\GuestHouseCancellationPolicy;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Models\GuestHouseAmenity;
use App\Models\TaxRate;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GuestHouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Photos')
                ->description('The cover photo appears on listing cards across the homepage and search results. Gallery photos appear on the stay detail page.')
                ->schema([
                    FileUpload::make('thumbnail')
                        ->label('Cover photo (product card image)')
                        ->disk('public')
                        ->directory('guesthouses/thumbnails')
                        ->image()
                        ->maxSize(8192)
                        ->helperText('Shown on product cards. Use a wide 16:9 image when possible.'),
                    FileUpload::make('gallery_paths')
                        ->label('Gallery photos')
                        ->disk('public')
                        ->directory('guesthouses/gallery')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->panelLayout('grid')
                        ->maxSize(8192)
                        ->helperText('Select multiple photos at once. Drag thumbnails to reorder, first image is used as cover when none is set above.'),
                ]),

            Section::make('Product card')
                ->description('These fields populate the listing card visitors see in Hand-picked stays, guesthouse search, and the homepage stay section.')
                ->schema([
                    Placeholder::make('product_card_specs_hint')
                        ->label('Card specs preview')
                        ->content('The card shows four specs: Sleeps (guests), Rooms (bedrooms), Bath (bathrooms), and City.'),
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('slug')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Leave blank to generate from the name. Used in the URL: /guesthouses/your-slug'),
                        Select::make('status')
                            ->options(collect(GuestHouseStatus::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)]))
                            ->required()
                            ->default(GuestHouseStatus::Draft->value),
                        Select::make('type')
                            ->label('Property type')
                            ->options(collect(GuestHouseType::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)]))
                            ->required(),
                        Textarea::make('short_description')
                            ->label('Short description')
                            ->rows(2)
                            ->columnSpan(2)
                            ->helperText('Brief teaser; also used at the top of the detail page.'),
                    ]),
                    Grid::make(4)->schema([
                        TextInput::make('max_guests')
                            ->label('Sleeps (max guests)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(2),
                        TextInput::make('bedrooms')
                            ->label('Bedrooms')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->default(1),
                        TextInput::make('bathrooms')
                            ->label('Bathrooms')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('city')
                            ->label('City (card location)')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Shown as the location spec on the card.'),
                    ]),
                    TextInput::make('base_price_per_night_euros')
                        ->label('Nightly price (€)')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->step(0.01)
                        ->prefix('€')
                        ->helperText('Displayed as “From €XX / night” on product cards.'),
                ]),

            Section::make('Stay details')
                ->schema([
                    Textarea::make('description')
                        ->label('Full description')
                        ->rows(6)
                        ->columnSpanFull(),
                    Grid::make(3)->schema([
                        TextInput::make('beds')
                            ->label('Total beds')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('min_nights')
                            ->label('Minimum nights')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                        TextInput::make('max_nights')
                            ->label('Maximum nights')
                            ->numeric()
                            ->minValue(1),
                    ]),
                ]),

            Section::make('Pricing & fees')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('cleaning_fee_euros')
                            ->label('Cleaning fee (€)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€'),
                        TextInput::make('security_deposit_euros')
                            ->label('Security deposit (€)')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('€'),
                        Select::make('tax_rate_id')
                            ->label('Tax rate')
                            ->options(fn () => TaxRate::query()->pluck('name', 'id'))
                            ->searchable(),
                    ]),
                    Repeater::make('seasonalPrices')
                        ->relationship()
                        ->label('Seasonal prices')
                        ->schema([
                            TextInput::make('name')->required(),
                            DatePicker::make('date_from')->required(),
                            DatePicker::make('date_to')->required(),
                            TextInput::make('price_per_night')
                                ->label('Price / night (€)')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->step(0.01)
                                ->prefix('€')
                                ->formatStateUsing(fn ($state) => filled($state) ? round($state / 100, 2) : null)
                                ->dehydrateStateUsing(fn ($state) => (int) round(((float) ($state ?? 0)) * 100)),
                            TextInput::make('minimum_nights')->numeric()->minValue(1),
                        ])
                        ->columns(2)
                        ->collapsible(),
                ]),

            Section::make('House rules & check-in')
                ->schema([
                    Grid::make(3)->schema([
                        TimePicker::make('check_in_time')->default('15:00'),
                        TimePicker::make('check_out_time')->default('11:00'),
                        Select::make('cancellation_policy')
                            ->options(collect(GuestHouseCancellationPolicy::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),
                    ]),
                ]),

            Section::make('Address')
                ->description('Hosts normally add this via Google Places autocomplete in the host panel. Admins can edit the stored fields here.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('address')
                            ->label('Street address')
                            ->required()
                            ->maxLength(500)
                            ->columnSpan(2)
                            ->helperText('Shown on the stay detail page, e.g. Laugavegur 12.'),
                        TextInput::make('country')
                            ->default('Iceland')
                            ->required()
                            ->maxLength(255),
                    ]),
                ]),

            Section::make('Advanced coordinates')
                ->collapsed()
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('latitude')
                            ->numeric()
                            ->helperText('Optional. Usually filled automatically when the host picks an address.'),
                        TextInput::make('longitude')
                            ->numeric()
                            ->helperText('Optional. Used for Google Maps links on the listing page.'),
                    ]),
                ]),

            Section::make('Amenities')
                ->schema([
                    CheckboxList::make('amenities')
                        ->relationship('amenities', 'name')
                        ->options(fn () => GuestHouseAmenity::query()->orderBy('group')->orderBy('name')->pluck('name', 'id'))
                        ->bulkToggleable()
                        ->columns(3),
                ]),

            Section::make('SEO')
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta title')
                        ->maxLength(255)
                        ->helperText('Leave empty to auto-generate from the listing name.'),
                    Textarea::make('meta_description')
                        ->label('Meta description')
                        ->rows(3)
                        ->helperText('Leave empty to use the short description or full description.'),
                    FileUpload::make('og_image')
                        ->label('Share image (OG)')
                        ->disk('public')
                        ->directory('guesthouses/og')
                        ->image()
                        ->maxSize(8192)
                        ->helperText('Leave empty to use the cover photo.'),
                ])
                ->collapsible(),
        ]);
    }
}
