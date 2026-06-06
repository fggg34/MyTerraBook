<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class HomepageCms extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Homepage CMS';

    protected static ?string $title = 'Homepage CMS';

    protected static ?string $slug = 'homepage-cms';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHomeModern;

    protected string $view = 'filament.pages.homepage-cms';

    public function mount(): void
    {
        $this->redirect(SiteContentHub::getUrl(['tab' => 'home']));
    }
}
