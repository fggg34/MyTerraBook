<?php

namespace App\Filament\Resources\BookingRestrictions;

use App\Filament\Clusters\ImpactRentCluster;
use App\Filament\Resources\BookingRestrictions\Pages\CreateBookingRestriction;
use App\Filament\Resources\BookingRestrictions\Pages\EditBookingRestriction;
use App\Filament\Resources\BookingRestrictions\Pages\ListBookingRestrictions;
use App\Filament\Resources\BookingRestrictions\Schemas\BookingRestrictionForm;
use App\Filament\Resources\BookingRestrictions\Tables\BookingRestrictionsTable;
use App\Models\BookingRestriction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BookingRestrictionResource extends Resource
{
    protected static ?string $model = BookingRestriction::class;

    protected static ?string $cluster = ImpactRentCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 9;

    public static function form(Schema $schema): Schema
    {
        return BookingRestrictionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingRestrictionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingRestrictions::route('/'),
            'create' => CreateBookingRestriction::route('/create'),
            'edit' => EditBookingRestriction::route('/{record}/edit'),
        ];
    }
}
