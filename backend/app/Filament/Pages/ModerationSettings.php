<?php

namespace App\Filament\Pages;

use App\Filament\Pages\GlobalConfiguration as GlobalConfigurationPage;
use App\Filament\Resources\CustomFields\CustomFieldResource;
use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;

class ModerationSettings extends Page
{
    private const PARENT_LABEL = 'Settings';

    protected static ?string $title = 'Settings';

    protected static ?string $slug = 'moderation-settings';

    protected string $view = 'filament.pages.moderation-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    /**
     * @return array<NavigationItem>
     */
    public static function getNavigationItems(): array
    {
        $configurationUrl = GlobalConfigurationPage::getUrl();
        $paymentMethodsUrl = PaymentMethodResource::getUrl('index');
        $customFieldsUrl = CustomFieldResource::getUrl('index');

        $isSettingsActive = fn (): bool => request()->is('admin/global-configuration*')
            || request()->is('admin/payment-methods*')
            || request()->is('admin/custom-fields*');

        return [
            NavigationItem::make('moderation-settings')
                ->label(self::PARENT_LABEL)
                ->group('Moderation')
                ->icon(static::$navigationIcon)
                ->sort(2)
                ->url($configurationUrl)
                ->isActiveWhen($isSettingsActive),
            NavigationItem::make('moderation-configuration')
                ->label('Configuration')
                ->group('Moderation')
                ->parentItem(self::PARENT_LABEL)
                ->sort(1)
                ->url($configurationUrl)
                ->isActiveWhen(fn (): bool => request()->is('admin/global-configuration*')),
            NavigationItem::make('moderation-payment-methods')
                ->label('Payment Methods')
                ->group('Moderation')
                ->parentItem(self::PARENT_LABEL)
                ->sort(2)
                ->url($paymentMethodsUrl)
                ->isActiveWhen(fn (): bool => request()->is('admin/payment-methods*')),
            NavigationItem::make('moderation-custom-fields')
                ->label('Custom Fields')
                ->group('Moderation')
                ->parentItem(self::PARENT_LABEL)
                ->sort(3)
                ->url($customFieldsUrl)
                ->isActiveWhen(fn (): bool => request()->is('admin/custom-fields*')),
        ];
    }

    public function mount(): void
    {
        $url = GlobalConfigurationPage::getUrl();

        $this->redirect($url, navigate: FilamentView::hasSpaMode($url));
    }
}
