<?php

namespace App\Models\Concerns;

use App\Models\ListingReview;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasListingReviews
{
    public function listingReviews(): MorphMany
    {
        return $this->morphMany(ListingReview::class, 'reviewable');
    }
}
