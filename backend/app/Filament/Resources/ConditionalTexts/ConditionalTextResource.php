<?php

namespace App\Filament\Resources\ConditionalTexts;

use App\Filament\Clusters\ImpactRentCluster;
use App\Filament\Resources\ConditionalTexts\Pages\CreateConditionalText;
use App\Filament\Resources\ConditionalTexts\Pages\EditConditionalText;
use App\Filament\Resources\ConditionalTexts\Pages\ListConditionalTexts;
use App\Filament\Resources\ConditionalTexts\Schemas\ConditionalTextForm;
use App\Filament\Resources\ConditionalTexts\Tables\ConditionalTextsTable;
use App\Models\ConditionalText;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConditionalTextResource extends Resource
{
    protected static ?string $model = ConditionalText::class;

    protected static ?string $cluster = ImpactRentCluster::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ConditionalTextForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ConditionalTextsTable::configure($table);
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
            'index' => ListConditionalTexts::route('/'),
            'create' => CreateConditionalText::route('/create'),
            'edit' => EditConditionalText::route('/{record}/edit'),
        ];
    }
}
