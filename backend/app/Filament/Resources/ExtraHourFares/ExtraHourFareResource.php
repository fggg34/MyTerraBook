<?php

namespace App\Filament\Resources\ExtraHourFares;

use App\Filament\Resources\ExtraHourFares\Pages\CreateExtraHourFare;
use App\Filament\Resources\ExtraHourFares\Pages\EditExtraHourFare;
use App\Filament\Resources\ExtraHourFares\Pages\ListExtraHourFares;
use App\Filament\Resources\ExtraHourFares\Schemas\ExtraHourFareForm;
use App\Filament\Resources\ExtraHourFares\Tables\ExtraHourFaresTable;
use App\Models\ExtraHourFare;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ExtraHourFareResource extends Resource
{
    protected static ?string $model = ExtraHourFare::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    public static function form(Schema $schema): Schema
    {
        return ExtraHourFareForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExtraHourFaresTable::configure($table);
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
            'index' => ListExtraHourFares::route('/'),
            'create' => CreateExtraHourFare::route('/create'),
            'edit' => EditExtraHourFare::route('/{record}/edit'),
        ];
    }
}
