<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ImpactRentCluster;
use App\Services\Admin\AdminReportsService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Reports extends Page
{
    protected static ?string $cluster = ImpactRentCluster::class;

    protected string $view = 'filament.pages.reports';

    protected static ?string $title = 'Reports';

    protected static string|UnitEnum|null $navigationGroup = 'Advanced';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $slug = 'reports';

    public string $from = '';

    public string $to = '';

    public string $reportType = 'occupancy_ranking';

    /** @var array{confirmed_orders:int,revenue_cents:int,revenue:string,average_order_value_cents:int,average_order_value:string} */
    public array $revenueSummary = [];

    /** @var array<int, array{car_id:int,car_name:string,confirmed_orders:int,booked_hours:int,booked_days:float}> */
    public array $occupancyRanking = [];

    /** @var array<int, array{country:string,confirmed_orders:int,revenue_cents:int,revenue:string}> */
    public array $topCountries = [];

    /** @var array<int, array{price_type_id:?int,rate_plan_name:string,confirmed_orders:int,revenue_cents:int,revenue:string}> */
    public array $ratePlanRevenue = [];

    public function mount(AdminReportsService $reportsService): void
    {
        $this->from = now()->subDays(30)->toDateString();
        $this->to = now()->toDateString();
        $this->loadReportData($reportsService);
    }

    public function updatedFrom(AdminReportsService $reportsService): void
    {
        $this->loadReportData($reportsService);
    }

    public function updatedTo(AdminReportsService $reportsService): void
    {
        $this->loadReportData($reportsService);
    }

    private function loadReportData(AdminReportsService $reportsService): void
    {
        $from = Carbon::parse($this->from)->startOfDay();
        $to = Carbon::parse($this->to)->endOfDay();

        if ($to->lessThan($from)) {
            $to = $from->copy()->endOfDay();
            $this->to = $to->toDateString();
        }

        $payload = $reportsService->forPeriod($from, $to);

        $this->revenueSummary = $payload['revenue_summary'];
        $this->occupancyRanking = $payload['occupancy_ranking'];
        $this->topCountries = $payload['top_countries'];
        $this->ratePlanRevenue = $payload['rate_plan_revenue'];
    }
}
