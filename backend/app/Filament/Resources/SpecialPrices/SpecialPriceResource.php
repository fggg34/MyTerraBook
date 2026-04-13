<?php

namespace App\Filament\Resources\SpecialPrices;

use App\Filament\Resources\SpecialPrices\Pages\CreateSpecialPrice;
use App\Filament\Resources\SpecialPrices\Pages\EditSpecialPrice;
use App\Filament\Resources\SpecialPrices\Pages\ListSpecialPrices;
use App\Filament\Resources\SpecialPrices\Schemas\SpecialPriceForm;
use App\Filament\Resources\SpecialPrices\Tables\SpecialPricesTable;
use App\Models\SpecialPrice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SpecialPriceResource extends Resource
{
    protected static ?string $model = SpecialPrice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    public static function form(Schema $schema): Schema
    {
        return SpecialPriceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpecialPricesTable::configure($table);
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
            'index' => ListSpecialPrices::route('/'),
            'create' => CreateSpecialPrice::route('/create'),
            'edit' => EditSpecialPrice::route('/{record}/edit'),
        ];
    }
}
