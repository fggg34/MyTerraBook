<?php

namespace App\Filament\Resources\DailyFares;

use App\Filament\Resources\DailyFares\Pages\CreateDailyFare;
use App\Filament\Resources\DailyFares\Pages\EditDailyFare;
use App\Filament\Resources\DailyFares\Pages\ListDailyFares;
use App\Filament\Resources\DailyFares\Schemas\DailyFareForm;
use App\Filament\Resources\DailyFares\Tables\DailyFaresTable;
use App\Models\DailyFare;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DailyFareResource extends Resource
{
    protected static ?string $model = DailyFare::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    public static function form(Schema $schema): Schema
    {
        return DailyFareForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailyFaresTable::configure($table);
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
            'index' => ListDailyFares::route('/'),
            'create' => CreateDailyFare::route('/create'),
            'edit' => EditDailyFare::route('/{record}/edit'),
        ];
    }
}
