<?php

namespace App\Filament\Clusters;

use App\Filament\Resources\Cars\CarResource;
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

    public function mount(): void
    {
        $url = CarResource::getUrl('index');

        $this->redirect($url, navigate: FilamentView::hasSpaMode($url));
    }
}
