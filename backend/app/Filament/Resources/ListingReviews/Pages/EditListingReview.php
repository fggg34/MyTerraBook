<?php

namespace App\Filament\Resources\ListingReviews\Pages;

use App\Filament\Resources\ListingReviews\ListingReviewResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditListingReview extends EditRecord
{
    protected static string $resource = ListingReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
