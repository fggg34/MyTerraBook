<?php

namespace App\Filament\Widgets;

use App\Services\Admin\AdminDashboardService;
use Filament\Widgets\Concerns\CanPoll;
use Filament\Widgets\Widget;
use Livewire\Attributes\Locked;

class WelcomeHeaderWidget extends Widget
{
    use CanPoll;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.welcome-header';

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }

    #[Locked]
    public ?string $chartDataChecksum = null;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $cachedChartData = null;

    public function mount(): void
    {
        $this->chartDataChecksum = $this->generateChartDataChecksum();
    }

    public function refreshDashboard(): void
    {
        $this->cachedChartData = null;
        $this->updateChartData();
    }

    public function updateChartData(): void
    {
        $this->cachedChartData = null;

        $newChecksum = $this->generateChartDataChecksum();

        if ($newChecksum !== $this->chartDataChecksum) {
            $this->chartDataChecksum = $newChecksum;

            $this->dispatch('updateChartData', data: $this->getChartData());
        }
    }

    public function rendering(): void
    {
        $this->updateChartData();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getChartData(): array
    {
        return $this->cachedChartData ??= app(AdminDashboardService::class)->operationsOverviewChart();
    }

    protected function generateChartDataChecksum(): string
    {
        return md5(json_encode($this->getChartData()));
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = auth()->user();
        $hour = (int) now()->format('G');

        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        $dashboard = app(AdminDashboardService::class);
        $snapshot = $dashboard->snapshot();
        $operations = $snapshot['operations'];
        $revenue = $snapshot['revenue'];

        $monthChange = $revenue['month_change_percent'];
        $monthChangeLabel = ($monthChange >= 0 ? '+' : '').$monthChange.'% vs last month';

        return [
            'greeting' => $greeting,
            'name' => $user?->name ?? 'Admin',
            'date' => now()->format('l, F j, Y'),
            'chartData' => $this->getChartData(),
            'chartOptions' => [
                'responsive' => true,
                'interaction' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
                'elements' => [
                    'line' => [
                        'borderJoinStyle' => 'round',
                        'borderCapStyle' => 'round',
                    ],
                    'point' => [
                        'hoverBorderWidth' => 2,
                    ],
                ],
                'plugins' => [
                    'legend' => [
                        'position' => 'bottom',
                        'align' => 'start',
                        'labels' => [
                            'boxWidth' => 10,
                            'usePointStyle' => true,
                            'pointStyle' => 'circle',
                            'padding' => 16,
                        ],
                    ],
                    'tooltip' => [
                        'enabled' => true,
                        'mode' => 'index',
                        'intersect' => false,
                    ],
                ],
                'scales' => [
                    'x' => [
                        'display' => true,
                        'grid' => [
                            'display' => false,
                        ],
                        'ticks' => [
                            'maxRotation' => 0,
                            'autoSkip' => true,
                            'maxTicksLimit' => 8,
                        ],
                    ],
                    'y' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'left',
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Active units',
                        ],
                        'grid' => [
                            'display' => true,
                        ],
                        'ticks' => [
                            'precision' => 0,
                            'stepSize' => 1,
                        ],
                    ],
                    'y1' => [
                        'type' => 'linear',
                        'display' => true,
                        'position' => 'right',
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Revenue (€)',
                        ],
                        'grid' => [
                            'drawOnChartArea' => false,
                        ],
                    ],
                ],
            ],
            'metrics' => [
                [
                    'label' => 'Active rentals',
                    'value' => (string) $operations['active_rentals'],
                    'description' => $operations['active_rentals'] === 1
                        ? '1 car out right now'
                        : $operations['active_rentals'].' cars out right now',
                    'color' => 'info',
                ],
                [
                    'label' => 'Guest stays',
                    'value' => (string) $operations['active_stays'],
                    'description' => $operations['active_stays'] === 1
                        ? '1 guest checked in today'
                        : $operations['active_stays'].' guests checked in today',
                    'color' => 'primary',
                ],
                [
                    'label' => 'Revenue this month',
                    'value' => '€'.$revenue['this_month_formatted'],
                    'description' => $monthChangeLabel,
                    'color' => $monthChange >= 0 ? 'success' : 'danger',
                ],
            ],
        ];
    }
}
