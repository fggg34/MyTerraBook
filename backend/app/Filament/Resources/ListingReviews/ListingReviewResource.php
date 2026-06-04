<?php

namespace App\Filament\Resources\ListingReviews;

use App\Filament\Clusters\ImpactRentCluster;
use App\Filament\Resources\ListingReviews\Pages\CreateListingReview;
use App\Filament\Resources\ListingReviews\Pages\EditListingReview;
use App\Filament\Resources\ListingReviews\Pages\ListListingReviews;
use App\Filament\Resources\ListingReviews\Schemas\ListingReviewForm;
use App\Filament\Resources\ListingReviews\Tables\ListingReviewsTable;
use App\Models\ListingReview;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ListingReviewResource extends Resource
{
    protected static ?string $model = ListingReview::class;

    protected static ?string $cluster = ImpactRentCluster::class;

    protected static ?string $navigationLabel = 'Listing reviews';

    protected static ?string $modelLabel = 'listing review';

    protected static ?string $pluralModelLabel = 'listing reviews';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ListingReviewForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ListingReviewsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListListingReviews::route('/'),
            'create' => CreateListingReview::route('/create'),
            'edit' => EditListingReview::route('/{record}/edit'),
        ];
    }
}
