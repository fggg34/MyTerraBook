<?php

namespace App\Filament\Resources\RentalConditions;

use App\Filament\Clusters\ImpactRentCluster;
use App\Filament\Resources\RentalConditions\Pages\CreateRentalCondition;
use App\Filament\Resources\RentalConditions\Pages\EditRentalCondition;
use App\Filament\Resources\RentalConditions\Pages\ListRentalConditions;
use App\Filament\Resources\RentalConditions\Schemas\RentalConditionForm;
use App\Filament\Resources\RentalConditions\Tables\RentalConditionsTable;
use App\Models\RentalCondition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RentalConditionResource extends Resource
{
    protected static ?string $model = RentalCondition::class;

    protected static ?string $cluster = ImpactRentCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Rental Conditions';

    protected static ?string $modelLabel = 'Rental Condition';

    protected static ?string $pluralModelLabel = 'Rental Conditions';

    public static function form(Schema $schema): Schema
    {
        return RentalConditionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentalConditionsTable::configure($table);
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
            'index' => ListRentalConditions::route('/'),
            'create' => CreateRentalCondition::route('/create'),
            'edit' => EditRentalCondition::route('/{record}/edit'),
        ];
    }
}
