<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ImpactRentCluster;
use App\Filament\Resources\TrackingCampaigns\TrackingCampaignResource;
use App\Services\Admin\TrackingStatisticsService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class TrackingStatistics extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = ImpactRentCluster::class;

    protected string $view = 'filament.pages.tracking-statistics';

    protected static ?string $title = 'Statistics Tracking';

    protected static string|UnitEnum|null $navigationGroup = 'Advanced';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBarSquare;

    protected static ?string $slug = 'tracking-statistics';

    public string $from = '';

    public string $to = '';

    public string $trackingDates = 'custom';

    public string $country = '';

    public string $referrer = '';

    public string $query = '';

    public string $activeTab = 'visitors';

    /** @var array<int, string> */
    public array $countryOptions = [];

    /** @var array<int, string> */
    public array $referrerOptions = [];

    /** @var array<int, array{date:string,requests:int,visitors:int}> */
    public array $mostDemandedDays = [];

    /** @var array{total_visitors:int,total_bookings:int,average_length_of_rent:float,average_conversion_rate:float} */
    public array $averageValues = [];

    /** @var array<int, array{referrer:string,visitors:int}> */
    public array $bestReferrers = [];

    /** @var array<int, array{date:string,visitors:int,bookings:int,conversion_rate:float}> */
    public array $conversionRates = [];

    public function mount(TrackingStatisticsService $statisticsService): void
    {
        $this->trackingDates = 'last_30_days';
        $this->applyPresetDateRange();
        $this->loadStatistics($statisticsService);
    }

    public function updatedTrackingDates(TrackingStatisticsService $statisticsService): void
    {
        $this->applyPresetDateRange();
        $this->loadStatistics($statisticsService);
    }

    public function updatedFrom(TrackingStatisticsService $statisticsService): void
    {
        $this->trackingDates = 'custom';
        $this->loadStatistics($statisticsService);
    }

    public function updatedTo(TrackingStatisticsService $statisticsService): void
    {
        $this->trackingDates = 'custom';
        $this->loadStatistics($statisticsService);
    }

    public function updatedCountry(TrackingStatisticsService $statisticsService): void
    {
        $this->loadStatistics($statisticsService);
    }

    public function updatedReferrer(TrackingStatisticsService $statisticsService): void
    {
        $this->loadStatistics($statisticsService);
    }

    public function updatedQuery(TrackingStatisticsService $statisticsService): void
    {
        $this->loadStatistics($statisticsService);
    }

    public function clearFilters(TrackingStatisticsService $statisticsService): void
    {
        $this->trackingDates = 'last_30_days';
        $this->country = '';
        $this->referrer = '';
        $this->query = '';
        $this->applyPresetDateRange();
        $this->loadStatistics($statisticsService);
    }

    public function getTrackingSettingsUrlProperty(): string
    {
        return TrackingCampaignResource::getUrl('index');
    }

    private function applyPresetDateRange(): void
    {
        $today = now();

        if ($this->trackingDates === 'today') {
            $this->from = $today->copy()->toDateString();
            $this->to = $today->copy()->toDateString();

            return;
        }

        if ($this->trackingDates === 'last_7_days') {
            $this->from = $today->copy()->subDays(6)->toDateString();
            $this->to = $today->copy()->toDateString();

            return;
        }

        if ($this->trackingDates === 'last_30_days') {
            $this->from = $today->copy()->subDays(29)->toDateString();
            $this->to = $today->copy()->toDateString();

            return;
        }

        if ($this->trackingDates === 'this_month') {
            $this->from = $today->copy()->startOfMonth()->toDateString();
            $this->to = $today->copy()->endOfMonth()->toDateString();
        }
    }

    private function loadStatistics(TrackingStatisticsService $statisticsService): void
    {
        $from = Carbon::parse($this->from)->startOfDay();
        $to = Carbon::parse($this->to)->endOfDay();

        if ($to->lessThan($from)) {
            $to = $from->copy()->endOfDay();
            $this->to = $to->toDateString();
        }

        $payload = $statisticsService->forPeriod(
            from: $from,
            to: $to,
            country: $this->country !== '' ? $this->country : null,
            referrer: $this->referrer !== '' ? $this->referrer : null,
            search: $this->query !== '' ? $this->query : null,
        );

        $this->countryOptions = $payload['countries'];
        $this->referrerOptions = $payload['referrers'];
        $this->mostDemandedDays = $payload['most_demanded_days'];
        $this->averageValues = $payload['average_values'];
        $this->bestReferrers = $payload['best_referrers'];
        $this->conversionRates = $payload['conversion_rates'];
    }
}
