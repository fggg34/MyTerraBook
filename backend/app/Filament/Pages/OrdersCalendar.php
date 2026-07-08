<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ImpactRentCluster;
use App\Support\AdminCalendarEmbed;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class OrdersCalendar extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $cluster = ImpactRentCluster::class;

    protected string $view = 'filament.partials.admin-calendar-embed';

    protected static ?string $title = 'Orders Calendar';

    protected static string|UnitEnum|null $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 2;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $slug = 'orders-calendar';

    public string $calendarEmbedUrl = '';

    public function mount(): void
    {
        $this->calendarEmbedUrl = AdminCalendarEmbed::embedUrlFor(auth()->user());
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }
}
