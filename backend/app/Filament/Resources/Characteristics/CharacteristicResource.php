<?php

namespace App\Filament\Resources\Characteristics;

use App\Filament\Resources\Characteristics\Pages\CreateCharacteristic;
use App\Filament\Resources\Characteristics\Pages\EditCharacteristic;
use App\Filament\Resources\Characteristics\Pages\ListCharacteristics;
use App\Filament\Resources\Characteristics\Schemas\CharacteristicForm;
use App\Filament\Resources\Characteristics\Tables\CharacteristicsTable;
use App\Models\Characteristic;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CharacteristicResource extends Resource
{
    protected static ?string $model = Characteristic::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Cars';

    public static function form(Schema $schema): Schema
    {
        return CharacteristicForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CharacteristicsTable::configure($table);
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
            'index' => ListCharacteristics::route('/'),
            'create' => CreateCharacteristic::route('/create'),
            'edit' => EditCharacteristic::route('/{record}/edit'),
        ];
    }
}
