<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardService;
use Filament\Widgets\ChartWidget;

class OperationsOverviewChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = '30-day operations overview';

    protected ?string $description = 'Daily active units and revenue trend';

    protected ?string $maxHeight = '320px';

    protected string $view = 'filament.widgets.operations-overview-chart';

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        return app(AdminDashboardService::class)->operationsOverviewChart();
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getOptions(): ?array
    {
        return app(AdminDashboardService::class)->operationsOverviewChartOptions($this->getCachedData());
    }

    public function hasChartActivity(): bool
    {
        return app(AdminDashboardService::class)->operationsOverviewChartHasActivity($this->getCachedData());
    }
}
