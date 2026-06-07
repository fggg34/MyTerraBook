<?php

namespace App\Filament\Widgets;

use App\Filament\GuestHouse\Resources\GuestHouseResource;
use App\Filament\Resources\Cars\CarResource;
use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
use App\Services\Admin\AdminDashboardService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CatalogAndUsersStats extends StatsOverviewWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Catalog, users & marketing';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $data = app(AdminDashboardService::class)->snapshot();
        $catalog = $data['catalog'];
        $traffic = $data['traffic'];

        return [
            Stat::make('Active cars', (string) $catalog['active_cars'])
                ->description('Fleet available for rent')
                ->icon(Heroicon::OutlinedTruck)
                ->color('primary')
                ->url(CarResource::getUrl('index')),

            Stat::make('Active guest houses', (string) $catalog['active_guest_houses'])
                ->description('Published listings')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->color('info')
                ->url(GuestHouseResource::getUrl('index')),

            Stat::make('Customers', (string) $catalog['customers'])
                ->description($catalog['hosts'].' hosts on platform')
                ->icon(Heroicon::OutlinedUsers)
                ->color('success'),

            Stat::make('Newsletter subscribers', (string) $traffic['newsletter_subscribers'])
                ->description('+'.$traffic['new_subscribers_this_month'].' this month')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('warning')
                ->url(NewsletterSubscriberResource::getUrl('index')),
        ];
    }
}
