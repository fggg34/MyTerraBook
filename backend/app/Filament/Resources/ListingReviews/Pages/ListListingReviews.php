<?php

namespace App\Filament\Resources\ListingReviews\Pages;

use App\Filament\Concerns\EmbedsCatalogTable;
use App\Filament\Resources\ListingReviews\ListingReviewResource;
use App\Models\ListingReview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListListingReviews extends ListRecords
{
    use EmbedsCatalogTable;

    protected static string $resource = ListingReviewResource::class;

    public function mount(): void
    {
        parent::mount();
        $this->tableFilters = array_replace_recursive([
            'reviewable_type' => ['value' => null],
            'rating' => ['value' => null],
            'is_approved' => ['value' => null],
        ], $this->tableFilters ?? []);
    }

    public function toggleVisible(int $id): void
    {
        $review = ListingReview::query()->find($id);
        if ($review) {
            $review->update(['is_approved' => ! $review->is_approved]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with('reviewable');
    }
}
