<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardService;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\Widget;

class DashboardInsightsWidget extends Widget
{
    use CanPoll;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        '@xl' => 4,
    ];

    protected string $view = 'filament.widgets.dashboard-insights';

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $snapshot = app(AdminDashboardService::class)->snapshot();

        return [
            'topCountries' => $snapshot['top_countries'],
        ];
    }
}
