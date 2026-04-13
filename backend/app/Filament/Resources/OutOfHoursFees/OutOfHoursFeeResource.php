<?php

namespace App\Filament\Resources\OutOfHoursFees;

use App\Filament\Resources\OutOfHoursFees\Pages\CreateOutOfHoursFee;
use App\Filament\Resources\OutOfHoursFees\Pages\EditOutOfHoursFee;
use App\Filament\Resources\OutOfHoursFees\Pages\ListOutOfHoursFees;
use App\Filament\Resources\OutOfHoursFees\Schemas\OutOfHoursFeeForm;
use App\Filament\Resources\OutOfHoursFees\Tables\OutOfHoursFeesTable;
use App\Models\OutOfHoursFee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class OutOfHoursFeeResource extends Resource
{
    protected static ?string $model = OutOfHoursFee::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    public static function form(Schema $schema): Schema
    {
        return OutOfHoursFeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OutOfHoursFeesTable::configure($table);
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
            'index' => ListOutOfHoursFees::route('/'),
            'create' => CreateOutOfHoursFee::route('/create'),
            'edit' => EditOutOfHoursFee::route('/{record}/edit'),
        ];
    }
}
