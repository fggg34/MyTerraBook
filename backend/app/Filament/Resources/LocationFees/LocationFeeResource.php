<?php

namespace App\Filament\Resources\LocationFees;

use App\Filament\Clusters\ImpactRentCluster;
use App\Filament\Resources\LocationFees\Pages\CreateLocationFee;
use App\Filament\Resources\LocationFees\Pages\EditLocationFee;
use App\Filament\Resources\LocationFees\Pages\ListLocationFees;
use App\Filament\Resources\LocationFees\Schemas\LocationFeeForm;
use App\Filament\Resources\LocationFees\Tables\LocationFeesTable;
use App\Models\LocationFee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LocationFeeResource extends Resource
{
    protected static ?string $model = LocationFee::class;

    protected static ?string $cluster = ImpactRentCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return LocationFeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocationFeesTable::configure($table);
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
            'index' => ListLocationFees::route('/'),
            'create' => CreateLocationFee::route('/create'),
            'edit' => EditLocationFee::route('/{record}/edit'),
        ];
    }
}
