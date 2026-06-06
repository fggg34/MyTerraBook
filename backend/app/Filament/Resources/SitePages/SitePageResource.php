<?php

namespace App\Filament\Resources\SitePages;

use App\Filament\Resources\SitePages\Pages\EditSitePage;
use App\Filament\Resources\SitePages\Pages\ListSitePages;
use App\Filament\Resources\SitePages\Schemas\SitePageForm;
use App\Filament\Resources\SitePages\Tables\SitePagesTable;
use App\Models\SitePage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SitePageResource extends Resource
{
    protected static ?string $model = SitePage::class;

    protected static ?string $navigationLabel = 'Site pages';

    protected static ?string $modelLabel = 'site page';

    protected static ?string $pluralModelLabel = 'site pages';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SitePageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SitePagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSitePages::route('/'),
            'edit' => EditSitePage::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
