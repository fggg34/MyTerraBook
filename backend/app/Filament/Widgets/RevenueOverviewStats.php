<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\Reports;
use App\Filament\Pages\TrackingStatistics;
use App\Filament\Resources\Orders\OrderResource;
use App\Services\Admin\AdminDashboardService;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RevenueOverviewStats extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Revenue & performance';

    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $data = app(AdminDashboardService::class)->snapshot();
        $revenue = $data['revenue'];
        $traffic = $data['traffic'];
        $operations = $data['operations'];

        $change = $revenue['month_change_percent'];
        $changeLabel = ($change >= 0 ? '+' : '').$change.'% vs last month';

        return [
            Stat::make('Total platform revenue', '€'.$revenue['total_formatted'])
                ->description($changeLabel)
                ->descriptionIcon($change >= 0 ? Heroicon::OutlinedArrowTrendingUp : Heroicon::OutlinedArrowTrendingDown)
                ->descriptionColor($change >= 0 ? 'success' : 'danger')
                ->icon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->url(Reports::getUrl()),

            Stat::make('Revenue this month', '€'.$revenue['this_month_formatted'])
                ->description('Car rentals and guest house bookings')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->color('primary')
                ->url(Reports::getUrl()),

            Stat::make('Active car rentals', (string) $operations['active_rentals'])
                ->description('Cars out right now')
                ->icon(Heroicon::OutlinedTruck)
                ->color('info')
                ->url(OrderResource::getUrl('index')),

            Stat::make('Website visitors (30d)', (string) $traffic['visitors_30d'])
                ->description(number_format($traffic['conversion_rate_30d'], 1).'% conversion rate')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->color('warning')
                ->url(TrackingStatistics::getUrl()),
        ];
    }
}
