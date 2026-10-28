<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\DashboardHomeWidget;
use App\Filament\Widgets\WelcomeHeaderWidget;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'Dashboard';

    protected Width|string|null $maxContentWidth = Width::Full;

    public static function canAccess(): bool
    {
        return auth()->user()?->isFullAdmin() ?? false;
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        return array_map(
            fn (NavigationItem $item): NavigationItem => $item->extraAttributes([
                'class' => 'fi-sidebar-item-home',
            ]),
            parent::getNavigationItems(),
        );
    }

    /**
     * @return array<class-string>
     */
    public function getWidgets(): array
    {
        return [
            WelcomeHeaderWidget::class,
            DashboardHomeWidget::class,
        ];
    }

    /**
     * @return int | array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            '@xl' => 12,
        ];
    }
}
