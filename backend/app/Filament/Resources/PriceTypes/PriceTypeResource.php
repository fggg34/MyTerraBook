<?php

namespace App\Filament\Resources\PriceTypes;

use App\Filament\Resources\PriceTypes\Pages\CreatePriceType;
use App\Filament\Resources\PriceTypes\Pages\EditPriceType;
use App\Filament\Resources\PriceTypes\Pages\ListPriceTypes;
use App\Filament\Resources\PriceTypes\Schemas\PriceTypeForm;
use App\Filament\Resources\PriceTypes\Tables\PriceTypesTable;
use App\Models\PriceType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PriceTypeResource extends Resource
{
    protected static ?string $model = PriceType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Rental settings';

    public static function form(Schema $schema): Schema
    {
        return PriceTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceTypesTable::configure($table);
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
            'index' => ListPriceTypes::route('/'),
            'create' => CreatePriceType::route('/create'),
            'edit' => EditPriceType::route('/{record}/edit'),
        ];
    }
}
