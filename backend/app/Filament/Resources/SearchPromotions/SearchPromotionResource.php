<?php

namespace App\Filament\Resources\SearchPromotions;

use App\Filament\Resources\SearchPromotions\Pages\CreateSearchPromotion;
use App\Filament\Resources\SearchPromotions\Pages\EditSearchPromotion;
use App\Filament\Resources\SearchPromotions\Pages\ListSearchPromotions;
use App\Filament\Resources\SearchPromotions\Schemas\SearchPromotionForm;
use App\Filament\Resources\SearchPromotions\Tables\SearchPromotionsTable;
use App\Models\SearchPromotion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SearchPromotionResource extends Resource
{
    protected static ?string $model = SearchPromotion::class;

    protected static ?string $navigationLabel = 'Search promotions';

    protected static ?string $modelLabel = 'search promotion';

    protected static ?string $pluralModelLabel = 'Search promotions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return SearchPromotionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SearchPromotionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSearchPromotions::route('/'),
            'create' => CreateSearchPromotion::route('/create'),
            'edit' => EditSearchPromotion::route('/{record}/edit'),
        ];
    }
}
