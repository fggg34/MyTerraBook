<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ListingReviewPage;
use App\Filament\Pages\Reports;
use App\Filament\Pages\TrackingStatistics;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\Admin\AdminDashboardService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected int|array|null $columns = 3;

    protected ?string $pollingInterval = '60s';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $data = app(AdminDashboardService::class)->snapshot();
        $revenue = $data['revenue'];
        $operations = $data['operations'];
        $moderation = $data['moderation'];
        $volume = $data['volume'];
        $traffic = $data['traffic'];

        $change = $revenue['month_change_percent'];
        $changeLabel = ($change >= 0 ? '+' : '').$change.'% vs last month';
        $pendingApprovals = $moderation['pending_listing_approvals'];
        $pendingOrders = $operations['pending_car_orders'];
        $bookingsThisMonth = $volume['car_orders_this_month'] + $volume['gh_bookings_this_month'];
        $liveOperations = $operations['active_rentals'] + $operations['active_stays'];

        return [
            Stat::make('Pending approvals', (string) $pendingApprovals)
                ->description($pendingApprovals > 0 ? 'Listings awaiting review' : 'Queue clear')
                ->descriptionColor($pendingApprovals > 0 ? 'primary' : 'success')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->color($pendingApprovals > 0 ? 'primary' : 'success')
                ->url(ListingReviewPage::getUrl()),

            Stat::make('Pending car orders', (string) $pendingOrders)
                ->description($pendingOrders > 0 ? 'Needs review' : 'All clear')
                ->descriptionColor($pendingOrders > 0 ? 'primary' : 'success')
                ->icon(Heroicon::OutlinedClock)
                ->color($pendingOrders > 0 ? 'primary' : 'success')
                ->url(OrderResource::getUrl('index')),

            Stat::make('Revenue this month', '€'.$revenue['this_month_formatted'])
                ->description($changeLabel)
                ->descriptionIcon($change >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->descriptionColor($change >= 0 ? 'success' : 'danger')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('primary')
                ->url(Reports::getUrl()),

            Stat::make('Live operations', (string) $liveOperations)
                ->description($operations['active_rentals'].' rentals · '.$operations['active_stays'].' stays')
                ->icon(Heroicon::OutlinedTruck)
                ->color('info')
                ->url(OrderResource::getUrl('index')),

            Stat::make('Bookings this month', (string) $bookingsThisMonth)
                ->description($volume['car_orders_this_month'].' car · '.$volume['gh_bookings_this_month'].' guest house')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->color('success'),

            Stat::make('Website visitors', (string) $traffic['visitors_30d'])
                ->description(number_format($traffic['conversion_rate_30d'], 1).'% conversion (30d)')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->color('primary')
                ->url(TrackingStatistics::getUrl()),
        ];
    }
}
