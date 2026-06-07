<?php

namespace App\Filament\Widgets;

use App\Filament\GuestHouse\Resources\GuestHouseBookingResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\Admin\AdminDashboardService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsStats extends StatsOverviewWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Live operations';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $data = app(AdminDashboardService::class)->snapshot();
        $operations = $data['operations'];
        $volume = $data['volume'];
        $revenue = $data['revenue'];

        return [
            Stat::make('Pending car orders', (string) $operations['pending_car_orders'])
                ->description($operations['pending_car_orders'] > 0 ? 'Needs review' : 'All clear')
                ->descriptionColor($operations['pending_car_orders'] > 0 ? 'warning' : 'success')
                ->icon(Heroicon::OutlinedClock)
                ->color($operations['pending_car_orders'] > 0 ? 'warning' : 'success')
                ->url(OrderResource::getUrl('index')),

            Stat::make('Active guest stays', (string) $operations['active_stays'])
                ->description('Guests checked in today')
                ->icon(Heroicon::OutlinedHomeModern)
                ->color('info')
                ->url(GuestHouseBookingResource::getUrl('index')),

            Stat::make('New bookings this month', (string) ($volume['car_orders_this_month'] + $volume['gh_bookings_this_month']))
                ->description($volume['car_orders_this_month'].' car · '.$volume['gh_bookings_this_month'].' guest house')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->color('primary'),

            Stat::make('Guest house occupancy', $revenue['gh_occupancy_rate'].'%')
                ->description('Confirmed nights this month')
                ->icon(Heroicon::OutlinedChartBar)
                ->color('success')
                ->url(GuestHouseBookingResource::getUrl('index')),
        ];
    }
}
