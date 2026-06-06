<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class FooterSettings extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Footer settings';

    protected static ?string $title = 'Footer settings';

    protected static ?string $slug = 'footer-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3BottomLeft;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected string $view = 'filament.pages.footer-settings';

    public function mount(): void
    {
        $this->redirect(SiteContentHub::getUrl(['tab' => 'global']));
    }
}
