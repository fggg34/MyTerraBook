<?php

namespace App\Filament\GuestHouse\Resources;

use App\Enums\GuestHouseCancellationPolicy;
use App\Enums\GuestHouseStatus;
use App\Enums\GuestHouseType;
use App\Filament\Clusters\GuestHouseCluster;
use App\Filament\GuestHouse\Resources\GuestHouseResource\Pages\CreateGuestHouse;
use App\Filament\GuestHouse\Resources\GuestHouseResource\Pages\EditGuestHouse;
use App\Filament\GuestHouse\Resources\GuestHouseResource\Pages\ListGuestHouses;
use App\Models\GuestHouse;
use App\Models\GuestHouseAmenity;
use App\Models\TaxRate;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class GuestHouseResource extends Resource
{
    protected static ?string $model = GuestHouse::class;

    protected static ?string $cluster = GuestHouseCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Properties';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Details')->schema([
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('slug')->maxLength(255)->unique(ignoreRecord: true),
                Select::make('type')->options(collect(GuestHouseType::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)]))->required(),
                Select::make('status')->options(collect(GuestHouseStatus::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)]))->required(),
                Textarea::make('short_description')->rows(2),
                Textarea::make('description')->rows(5),
                TextInput::make('max_guests')->numeric()->required()->default(2),
                TextInput::make('bedrooms')->numeric()->required()->default(1),
                TextInput::make('bathrooms')->numeric()->required()->default(1),
                TextInput::make('beds')->numeric()->required()->default(1),
            ])->columns(2),
            Section::make('Pricing')->schema([
                TextInput::make('base_price_per_night')->label('Base price / night (cents)')->numeric()->required(),
                TextInput::make('cleaning_fee')->label('Cleaning fee (cents)')->numeric(),
                TextInput::make('security_deposit')->label('Security deposit (cents)')->numeric(),
                TextInput::make('min_nights')->numeric()->default(1)->required(),
                TextInput::make('max_nights')->numeric(),
                Select::make('tax_rate_id')->label('Tax rate')->options(fn () => TaxRate::query()->pluck('name', 'id')),
                Repeater::make('seasonalPrices')->relationship()->schema([
                    TextInput::make('name')->required(),
                    DatePicker::make('date_from')->required(),
                    DatePicker::make('date_to')->required(),
                    TextInput::make('price_per_night')->label('Price / night (cents)')->numeric()->required(),
                    TextInput::make('minimum_nights')->numeric(),
                ])->columns(2)->collapsible(),
            ]),
            Section::make('Policies')->schema([
                TimePicker::make('check_in_time')->default('15:00'),
                TimePicker::make('check_out_time')->default('11:00'),
                Select::make('cancellation_policy')->options(collect(GuestHouseCancellationPolicy::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),
            ])->columns(3),
            Section::make('Location')->schema([
                TextInput::make('address'),
                TextInput::make('city'),
                TextInput::make('country'),
                TextInput::make('latitude')->numeric(),
                TextInput::make('longitude')->numeric(),
            ])->columns(2),
            Section::make('Media')->schema([
                TextInput::make('thumbnail')->label('Thumbnail URL / path'),
                Repeater::make('images')->relationship()->schema([
                    TextInput::make('path')->required(),
                    TextInput::make('caption'),
                    TextInput::make('sort_order')->numeric()->default(0),
                ])->orderColumn('sort_order')->collapsible(),
            ]),
            Section::make('Amenities')->schema([
                CheckboxList::make('amenities')
                    ->relationship('amenities', 'name')
                    ->options(fn () => GuestHouseAmenity::query()->orderBy('group')->orderBy('name')->pluck('name', 'id'))
                    ->columns(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('city')->searchable(),
                TextColumn::make('base_price_per_night')
                    ->label('Price/night')
                    ->formatStateUsing(fn ($state) => '€ '.number_format($state / 100, 2)),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                SelectFilter::make('type')->options(collect(GuestHouseType::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),
                SelectFilter::make('status')->options(collect(GuestHouseStatus::cases())->mapWithKeys(fn ($c) => [$c->value => ucfirst($c->value)])),
                SelectFilter::make('city')->options(fn () => GuestHouse::query()->whereNotNull('city')->distinct()->pluck('city', 'city')),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGuestHouses::route('/'),
            'create' => CreateGuestHouse::route('/create'),
            'edit' => EditGuestHouse::route('/{record}/edit'),
        ];
    }
}
