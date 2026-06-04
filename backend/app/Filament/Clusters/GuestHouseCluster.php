<?php

namespace App\Filament\Clusters;

use App\Filament\GuestHouse\Resources\GuestHouseResource;
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

    public function mount(): void
    {
        $url = GuestHouseResource::getUrl('index');

        $this->redirect($url, navigate: FilamentView::hasSpaMode($url));
    }
}
