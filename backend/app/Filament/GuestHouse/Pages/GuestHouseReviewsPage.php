<?php

namespace App\Filament\GuestHouse\Pages;

use App\Filament\Clusters\GuestHouseCluster;
use App\Filament\Resources\ListingReviews\ListingReviewResource;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Shortcut inside Guest Houses cluster → unified listing reviews admin.
 */
class GuestHouseReviewsPage extends Page
{
    protected static ?string $cluster = GuestHouseCluster::class;

    protected static ?string $navigationLabel = 'Guest reviews';

    protected static ?string $title = 'Listing reviews';

    protected static ?string $slug = 'guest-reviews';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.redirect-placeholder';

    public function mount(): void
    {
        $url = ListingReviewResource::getUrl('index');

        $this->redirect($url, navigate: FilamentView::hasSpaMode($url));
    }
}
