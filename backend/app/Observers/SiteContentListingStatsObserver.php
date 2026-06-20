<?php

namespace App\Observers;

use App\Services\SiteContentService;
use Illuminate\Database\Eloquent\Model;

class SiteContentListingStatsObserver
{
    public function __construct(
        private readonly SiteContentService $siteContent,
    ) {}

    public function saved(Model $model): void
    {
        $this->siteContent->clearCache();
    }

    public function deleted(Model $model): void
    {
        $this->siteContent->clearCache();
    }
}
