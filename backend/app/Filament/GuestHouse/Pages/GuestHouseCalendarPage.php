<?php

namespace App\Filament\GuestHouse\Pages;

use App\Filament\Clusters\GuestHouseCluster;
use App\Support\AdminCalendarEmbed;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class GuestHouseCalendarPage extends Page
{
    protected static ?string $cluster = GuestHouseCluster::class;

    protected string $view = 'filament.guest-house.pages.calendar';

    protected static ?string $title = 'Bookings Calendar';

    protected static string|UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $slug = 'calendar';

    public string $calendarEmbedUrl = '';

    public function mount(): void
    {
        $this->calendarEmbedUrl = AdminCalendarEmbed::embedUrlFor(auth()->user());
    }
}
