<?php

namespace App\Filament\Resources\HourlyFares;

use App\Filament\Resources\HourlyFares\Pages\CreateHourlyFare;
use App\Filament\Resources\HourlyFares\Pages\EditHourlyFare;
use App\Filament\Resources\HourlyFares\Pages\ListHourlyFares;
use App\Filament\Resources\HourlyFares\Schemas\HourlyFareForm;
use App\Filament\Resources\HourlyFares\Tables\HourlyFaresTable;
use App\Models\HourlyFare;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HourlyFareResource extends Resource
{
    protected static ?string $model = HourlyFare::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    public static function form(Schema $schema): Schema
    {
        return HourlyFareForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HourlyFaresTable::configure($table);
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
            'index' => ListHourlyFares::route('/'),
            'create' => CreateHourlyFare::route('/create'),
            'edit' => EditHourlyFare::route('/{record}/edit'),
        ];
    }
}
