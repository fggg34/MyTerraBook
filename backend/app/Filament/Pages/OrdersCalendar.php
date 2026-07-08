<?php

namespace App\Filament\Pages;

use App\Filament\Clusters\ImpactRentCluster;
use App\Support\AdminCalendarEmbed;
use App\Support\AdminCalendarEmbedAssets;
use App\Support\CalendarEmbedDebug;
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

    public ?string $handoffToken = null;

    public ?string $embedJsUrl = null;

    public ?string $embedCssUrl = null;

    public function mount(): void
    {
        $user = auth()->user();
        $this->handoffToken = AdminCalendarEmbed::createHandoffToken($user);
        [$this->embedJsUrl, $this->embedCssUrl] = AdminCalendarEmbedAssets::resolve();
        $this->calendarEmbedUrl = AdminCalendarEmbed::embedUrlFor($user);

        // #region agent log
        CalendarEmbedDebug::log(
            'OrdersCalendar.php:mount',
            'Filament calendar page mounted',
            [
                'userId' => $user?->id,
                'hasHandoff' => filled($this->handoffToken),
                'embedJsUrl' => $this->embedJsUrl,
                'embedCssUrl' => $this->embedCssUrl,
                'calendarEmbedUrl' => $this->calendarEmbedUrl,
                'useInline' => filled($this->embedJsUrl),
                'spaIndexPath' => config('spa.index_path'),
                'frontendUrl' => config('app.frontend_url'),
            ],
            'H1',
        );
        // #endregion
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }
}
