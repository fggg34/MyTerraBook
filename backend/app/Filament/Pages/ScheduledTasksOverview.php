<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ImpactRentCluster;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use UnitEnum;

class ScheduledTasksOverview extends Page
{
    protected static ?string $cluster = ImpactRentCluster::class;

    protected string $view = 'filament.pages.scheduled-tasks-overview';

    protected static ?string $title = 'Scheduled tasks';

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 5;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public string $scheduleList = '';

    public function mount(): void
    {
        Artisan::call('schedule:list');
        $this->scheduleList = trim(Artisan::output());
    }
}
