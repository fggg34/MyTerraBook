<?php

namespace App\Filament\Clusters;

use App\Enums\ListingApprovalStatus;
use App\Filament\Resources\Cars\CarResource;
use App\Models\Car;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;

class ImpactRentCluster extends Cluster
{
    protected static ?string $slug = 'impact-rent';

    protected static ?string $title = 'Impact Rent';

    protected static ?string $navigationLabel = 'Impact Rent';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    // The native cluster sub-navigation (grouped by navigation group: Catalog,
    // Platform, Network, Operations, Pricing, Marketing) is replaced by the
    // custom Impact Rent top bar (see impact-rent-editor-quick-access.blade.php),
    // so disable it here to avoid rendering a duplicate/outdated menu.
    protected static bool $shouldRegisterSubNavigation = false;

    public static function getNavigationBadge(): ?string
    {
        $count = Car::query()->where('listing_status', ListingApprovalStatus::PendingReview)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    public function mount(): void
    {
        $url = CarResource::getUrl('index');

        $this->redirect($url, navigate: FilamentView::hasSpaMode($url));
    }
}
