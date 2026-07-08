<?php

namespace App\Filament\GuestHouse\Pages;

use App\Filament\Clusters\GuestHouseCluster;
use App\Support\AdminCalendarEmbed;
use App\Support\AdminCalendarEmbedAssets;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class GuestHouseCalendarPage extends Page
{
    protected static ?string $cluster = GuestHouseCluster::class;

    protected string $view = 'filament.partials.admin-calendar-embed';

    protected static ?string $title = 'Bookings Calendar';

    protected static string|UnitEnum|null $navigationGroup = 'Bookings';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?string $slug = 'calendar';

    public string $calendarEmbedUrl = '';

    public ?string $handoffToken = null;

    public ?string $embedJsUrl = null;

    public ?string $embedCssUrl = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->handoffToken = AdminCalendarEmbed::createHandoffToken($user);
        [$this->embedJsUrl, $this->embedCssUrl] = AdminCalendarEmbedAssets::resolve();
        $this->calendarEmbedUrl = AdminCalendarEmbed::embedUrlFor($user);
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }
}
