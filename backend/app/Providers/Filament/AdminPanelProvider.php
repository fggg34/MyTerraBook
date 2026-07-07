<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\ListingReviews\ListingReviewResource;
use App\Http\Middleware\RestrictDesignerFilamentAccess;
use App\Services\Admin\AdminBrandingService;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile(EditProfile::class, isSimple: false)
            ->userMenuItems([
                'profile' => fn (Action $action): Action => $action
                    ->label('Profile settings')
                    ->icon(Heroicon::OutlinedCog6Tooth),
            ])
            ->brandName('')
            ->brandLogo(fn (): \Illuminate\Contracts\Support\Htmlable => app(AdminBrandingService::class)->logoHtml())
            ->brandLogoHeight('2.25rem')
            ->favicon(fn (): ?string => app(AdminBrandingService::class)->faviconUrl())
            ->font(
                'Open Sans',
                url: 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Quicksand:wght@700&display=swap',
            )
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::hex('#334e68'),
            ])
            ->darkMode(false, true)
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverResources(in: app_path('Filament/GuestHouse/Resources'), for: 'App\Filament\GuestHouse\Resources')
            ->resources([
                ListingReviewResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverPages(in: app_path('Filament/GuestHouse/Pages'), for: 'App\Filament\GuestHouse\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Marketing'),
                NavigationGroup::make('Content')
                    ->collapsible()
                    ->collapsed(),
                NavigationGroup::make('Moderation'),
                NavigationGroup::make('Email'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                ValidateCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RestrictDesignerFilamentAccess::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.panel.head-sidebar-migration')->render(),
            )
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => view('filament.panel.admin-theme')->render()
                    .view('filament.panel.sidebar-hover-peek')->render()
                    .view('filament.panel.sidebar-home-preview')->render()
                    .view('filament.panel.user-menu-styles')->render(),
            )
            ->renderHook(
                PanelsRenderHook::USER_MENU_PROFILE_BEFORE,
                fn (): string => view('filament.panel.user-menu-account-info')->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.panel.body-end-scripts')->render(),
            )
            ->renderHook(
                PanelsRenderHook::PAGE_SUB_NAVIGATION_TOP_AFTER,
                fn (): string => view('filament.panel.impact-rent-editor-quick-access')->render()
                    .view('filament.panel.guest-house-editor-quick-access')->render(),
            );
    }
}
