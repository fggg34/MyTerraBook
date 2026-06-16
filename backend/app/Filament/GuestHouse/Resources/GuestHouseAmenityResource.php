<?php

namespace App\Filament\GuestHouse\Resources;

use App\Filament\Clusters\GuestHouseCluster;
use App\Filament\GuestHouse\Resources\GuestHouseAmenityResource\Pages\ManageGuestHouseAmenities;
use App\Models\GuestHouseAmenity;
use App\Support\AdminTableBadgeColors;
use App\Support\IconCatalog;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use UnitEnum;

class GuestHouseAmenityResource extends Resource
{
    protected static ?string $model = GuestHouseAmenity::class;

    protected static ?string $cluster = GuestHouseCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Setup';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(255),
            Select::make('icon')
                ->label('Icon')
                ->options(IconCatalog::filamentOptions())
                ->searchable()
                ->allowHtml()
                ->native(false)
                ->preload()
                ->placeholder('Search and pick an icon'),
            TextInput::make('group')->maxLength(64),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('icon')
                    ->label('Icon')
                    ->placeholder('—')
                    ->formatStateUsing(function (?string $state): HtmlString|string {
                        if (! $state || ! function_exists('svg')) {
                            return '—';
                        }

                        try {
                            return new HtmlString(svg('lucide-'.$state, 'w-5 h-5')->toHtml());
                        } catch (\Throwable) {
                            return $state;
                        }
                    }),
                TextColumn::make('group')
                    ->badge()
                    ->color(fn (): string => AdminTableBadgeColors::neutral())
                    ->formatStateUsing(fn (mixed $state): string => AdminTableBadgeColors::humanize($state)),
            ])
            ->defaultSort('group');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGuestHouseAmenities::route('/'),
        ];
    }
}
