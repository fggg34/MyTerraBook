<?php

namespace App\Filament\Resources\RentalOptions;

use App\Filament\Resources\RentalOptions\Pages\CreateRentalOption;
use App\Filament\Resources\RentalOptions\Pages\EditRentalOption;
use App\Filament\Resources\RentalOptions\Pages\ListRentalOptions;
use App\Filament\Resources\RentalOptions\Schemas\RentalOptionForm;
use App\Filament\Resources\RentalOptions\Tables\RentalOptionsTable;
use App\Models\RentalOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RentalOptionResource extends Resource
{
    protected static ?string $model = RentalOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Cars';

    public static function form(Schema $schema): Schema
    {
        return RentalOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalOptionsTable::configure($table);
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
            'index' => ListRentalOptions::route('/'),
            'create' => CreateRentalOption::route('/create'),
            'edit' => EditRentalOption::route('/{record}/edit'),
        ];
    }
}
