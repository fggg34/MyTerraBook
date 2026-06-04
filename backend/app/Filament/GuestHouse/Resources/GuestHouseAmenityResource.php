<?php

namespace App\Filament\GuestHouse\Resources;

use App\Filament\Clusters\GuestHouseCluster;
use App\Filament\GuestHouse\Resources\GuestHouseAmenityResource\Pages\ManageGuestHouseAmenities;
use App\Models\GuestHouseAmenity;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
            TextInput::make('icon')->label('Icon (Lucide name)')->maxLength(64),
            TextInput::make('group')->maxLength(64),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('icon'),
                TextColumn::make('group')->badge(),
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
