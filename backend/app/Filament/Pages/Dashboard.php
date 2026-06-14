<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardInsightsWidget;
use App\Filament\Widgets\DashboardOverviewStats;
use App\Filament\Widgets\OperationsOverviewChartWidget;
use App\Filament\Widgets\RecentOrdersWidget;
use App\Filament\Widgets\WelcomeHeaderWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';

    public static function canAccess(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

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
            DashboardOverviewStats::class,
            OperationsOverviewChartWidget::class,
            RecentOrdersWidget::class,
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
