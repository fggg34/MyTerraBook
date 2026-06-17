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

    // Native cluster tabs are hidden in impact-rent-editor-quick-access.blade.php
    // and replaced by the custom Impact Rent bar in the same sub-navigation slot.

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
