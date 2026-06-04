<?php

namespace App\Filament\Resources\ListingReviews\Pages;

use App\Filament\Resources\ListingReviews\ListingReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListListingReviews extends ListRecords
{
    protected static string $resource = ListingReviewResource::class;

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
