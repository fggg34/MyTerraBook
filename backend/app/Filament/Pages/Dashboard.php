<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CatalogAndUsersStats;
use App\Filament\Widgets\DashboardInsightsWidget;
use App\Filament\Widgets\ModerationStats;
use App\Filament\Widgets\OperationsStats;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\RevenueOverviewStats;
use App\Filament\Widgets\WelcomeHeaderWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            WelcomeHeaderWidget::class,
            RecentOrdersWidget::class,
            RevenueOverviewStats::class,
            OperationsStats::class,
            CatalogAndUsersStats::class,
            ModerationStats::class,
            DashboardInsightsWidget::class,
        ];
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return 12;
    }
}
