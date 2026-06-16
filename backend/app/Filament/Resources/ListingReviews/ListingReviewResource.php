<?php

namespace App\Filament\Resources\ListingReviews;

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

    protected static ?string $slug = 'listing-reviews';

    protected static ?string $navigationLabel = 'Listing reviews';

    protected static ?string $modelLabel = 'listing review';

    protected static ?string $pluralModelLabel = 'listing reviews';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ListingReview::query()->where('is_approved', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Reviews awaiting moderation';
    }

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
