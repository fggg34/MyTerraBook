<?php

namespace App\Filament\Clusters;

use App\Enums\GuestHouseStatus;
use App\Filament\GuestHouse\Resources\GuestHouseResource;
use App\Models\GuestHouse;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;

class GuestHouseCluster extends Cluster
{
    protected static ?string $slug = 'guest-houses';

    protected static ?string $title = 'Guest Houses';

    protected static ?string $navigationLabel = 'Guest Houses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getNavigationBadge(): ?string
    {
        $count = GuestHouse::query()->where('status', GuestHouseStatus::PendingReview)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public function mount(): void
    {
        $url = GuestHouseResource::getUrl('index');

        $this->redirect($url, navigate: FilamentView::hasSpaMode($url));
    }
}
