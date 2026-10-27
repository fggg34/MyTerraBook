@php
    use App\Enums\OrderStatus;
    use App\Filament\GuestHouse\Pages\GuestHouseCalendarPage;
    use App\Filament\GuestHouse\Resources\GuestHouseAmenityResource;
    use App\Filament\GuestHouse\Resources\GuestHouseBookingResource;
    use App\Filament\GuestHouse\Resources\GuestHouseResource;
    use App\Filament\Pages\Dashboard;
    use App\Filament\Pages\EmailSettings;
    use App\Filament\Pages\EmailTesting;
    use App\Filament\Pages\FaresOverview;
    use App\Filament\Pages\GlobalConfiguration;
    use App\Filament\Pages\ListingReviewPage;
    use App\Filament\Pages\LoggedInUsers;
    use App\Filament\Pages\ManageUsers;
    use App\Filament\Pages\OrdersCalendar;
    use App\Filament\Pages\Reports;
    use App\Filament\Pages\SiteContentHub;
    use App\Filament\Pages\TrackingStatistics;
    use App\Filament\Resources\Backups\BackupResource;
    use App\Filament\Resources\BlogPosts\BlogPostResource;
    use App\Filament\Resources\BookingChangeRequests\BookingChangeRequestResource;
    use App\Filament\Resources\BookingRestrictions\BookingRestrictionResource;
    use App\Filament\Resources\Cars\CarResource;
    use App\Filament\Resources\CarUnits\CarUnitResource;
    use App\Filament\Resources\Categories\CategoryResource;
    use App\Filament\Resources\Characteristics\CharacteristicResource;
    use App\Filament\Resources\ConditionalTexts\ConditionalTextResource;
    use App\Filament\Resources\Coupons\CouponResource;
    use App\Filament\Resources\CustomFields\CustomFieldResource;
    use App\Filament\Resources\DailyFares\DailyFareResource;
    use App\Filament\Resources\EmailLogs\EmailLogResource;
    use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
    use App\Filament\Resources\ExtraHourFares\ExtraHourFareResource;
    use App\Filament\Resources\HourlyFares\HourlyFareResource;
    use App\Filament\Resources\ListingReviews\ListingReviewResource;
    use App\Filament\Resources\LocationFees\LocationFeeResource;
    use App\Filament\Resources\Locations\LocationResource;
    use App\Filament\Resources\NewsletterCampaigns\NewsletterCampaignResource;
    use App\Filament\Resources\NewsletterSubscribers\NewsletterSubscriberResource;
    use App\Filament\Resources\Orders\OrderResource;
    use App\Filament\Resources\OutOfHoursFees\OutOfHoursFeeResource;
    use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
    use App\Filament\Resources\PriceTypes\PriceTypeResource;
    use App\Filament\Resources\RegisteredClients\RegisteredClientResource;
    use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
    use App\Filament\Resources\RentalConditions\RentalConditionResource;
    use App\Filament\Resources\RentalOptions\RentalOptionResource;
    use App\Filament\Resources\SpecialPrices\SpecialPriceResource;
    use App\Filament\Resources\SubCategories\SubCategoryResource;
    use App\Filament\Resources\TaxRates\TaxRateResource;
    use App\Filament\Resources\TrackingCampaigns\TrackingCampaignResource;
    use App\Models\Order;

    $icon = fn (string $name): string => asset('admin-redesign/'.$name);

    $allowed = function (string $class): bool {
        try {
            if (is_subclass_of($class, \Filament\Resources\Resource::class)) {
                return $class::canViewAny();
            }

            return $class::canAccess();
        } catch (\Throwable) {
            return false;
        }
    };

    $href = function (string $class) use ($allowed): ?string {
        if (! $allowed($class)) {
            return null;
        }

        try {
            return $class::getUrl();
        } catch (\Throwable) {
            return null;
        }
    };

    $current = function (array $urls, bool $exact = false): bool {
        foreach ($urls as $url) {
            if (! is_string($url) || $url === '') {
                continue;
            }
            $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
            if ($path === '') {
                continue;
            }
            if ($exact ? request()->is($path) : request()->is($path, $path.'/*')) {
                return true;
            }
        }

        return false;
    };

    $pendingReservations = null;
    if ($allowed(OrderResource::class)) {
        try {
            $pendingReservations = Order::query()->where('order_status', OrderStatus::Pending)->count();
        } catch (\Throwable) {
            $pendingReservations = null;
        }
    }

    $comingSoon = false;
    try {
        $comingSoon = app(\App\Services\SitePreviewService::class)->isComingSoonEnabled();
    } catch (\Throwable) {
        $comingSoon = false;
    }

    $user = auth()->user();
    $name = trim((string) ($user->name ?? 'Admin'));
    $email = (string) ($user->email ?? '');
    $roleLabel = $user?->role?->name ?? 'Account';
    $parts = preg_split('/\s+/', $name) ?: [];
    $initials = '';
    foreach (array_slice(array_filter($parts), 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    $initials = $initials !== '' ? $initials : 'A';

    $firstUrl = fn (array $urls): ?string => array_values($urls)[0] ?? null;

    $vehicleUrls = array_filter([
        $href(CarResource::class),
        $href(CarUnitResource::class),
        $href(CategoryResource::class),
        $href(SubCategoryResource::class),
        $href(CharacteristicResource::class),
        $href(LocationResource::class),
        $href(RentalConditionResource::class),
    ]);
    $pricingUrls = array_filter([
        $href(SpecialPriceResource::class),
        $href(DailyFareResource::class),
        $href(BookingRestrictionResource::class),
        $href(FaresOverview::class),
        $href(ExtraHourFareResource::class),
        $href(HourlyFareResource::class),
        $href(OutOfHoursFeeResource::class),
        $href(LocationFeeResource::class),
        $href(RentalOptionResource::class),
        $href(CouponResource::class),
        $href(TaxRateResource::class),
        $href(PriceTypeResource::class),
    ]);
    $calendarUrls = array_filter([
        $href(OrdersCalendar::class),
        $href(GuestHouseCalendarPage::class),
    ]);
    $guestHouseUrls = array_filter([
        $href(GuestHouseResource::class),
        $href(GuestHouseAmenityResource::class),
    ]);
    $emailUrls = array_filter([
        $href(EmailTemplateResource::class),
        $href(EmailSettings::class),
        $href(EmailTesting::class),
        $href(EmailLogResource::class),
    ]);
    $analyticsUrls = array_filter([
        $href(Reports::class),
        $href(TrackingStatistics::class),
        $href(TrackingCampaignResource::class),
    ]);
    $settingsUrls = array_filter([
        $href(GlobalConfiguration::class),
        $href(PaymentMethodResource::class),
        $href(CustomFieldResource::class),
        $href(ManageUsers::class),
        $href(LoggedInUsers::class),
        $href(ConditionalTextResource::class),
        $href(BackupResource::class),
        filament()->getProfileUrl(),
    ]);
    $newsletterUrls = array_filter([
        $href(NewsletterSubscriberResource::class),
        $href(NewsletterCampaignResource::class),
    ]);

    $link = function (string $label, string $iconName, ?string $url, bool $active, ?int $badge = null): ?array {
        if (! is_string($url) || $url === '') {
            return null;
        }

        return ['label' => $label, 'icon' => $iconName, 'url' => $url, 'active' => $active, 'badge' => $badge];
    };

    $groups = [
        ['label' => null, 'items' => array_filter([
            $link('Home', 'icon-home.svg', $href(Dashboard::class), $current(array_filter([$href(Dashboard::class)]), true)),
        ])],
        ['label' => 'Bookings', 'items' => array_filter([
            $link('Calendar', 'icon-calendar.svg', $firstUrl($calendarUrls), $current($calendarUrls)),
            $link('Car reservations', 'icon-car.svg', $href(OrderResource::class), $current(array_filter([$href(OrderResource::class)])), $pendingReservations),
            $link('Guest house bookings', 'icon-stay.svg', $href(GuestHouseBookingResource::class), $current(array_filter([$href(GuestHouseBookingResource::class)]))),
            $link('Change requests', 'icon-change.svg', $href(BookingChangeRequestResource::class), $current(array_filter([$href(BookingChangeRequestResource::class)]))),
        ])],
        ['label' => 'Catalog', 'items' => array_filter([
            $link('Guest houses', 'icon-house.svg', $firstUrl($guestHouseUrls), $current($guestHouseUrls)),
            $link('Vehicles', 'icon-vehicle.svg', $firstUrl($vehicleUrls), $current($vehicleUrls)),
            $link('Pricing', 'icon-pricing.svg', $firstUrl($pricingUrls), $current($pricingUrls)),
        ])],
        ['label' => 'People', 'items' => array_filter([
            $link('Hosts', 'icon-hosts.svg', $href(RegisteredHostResource::class), $current(array_filter([$href(RegisteredHostResource::class)]))),
            $link('Clients', 'icon-clients.svg', $href(RegisteredClientResource::class), $current(array_filter([$href(RegisteredClientResource::class)]))),
        ])],
        ['label' => 'Moderation', 'items' => array_filter([
            $link('Listing approvals', 'icon-approvals.svg', $href(ListingReviewPage::class), $current(array_filter([$href(ListingReviewPage::class)]))),
            $link('Listing reviews', 'icon-reviews.svg', $href(ListingReviewResource::class), $current(array_filter([$href(ListingReviewResource::class), url('/admin/guest-houses/guest-reviews')]))),
        ])],
        ['label' => 'Website', 'items' => array_filter([
            $link('Site content', 'icon-content.svg', $href(SiteContentHub::class), $current(array_filter([$href(SiteContentHub::class), url('/admin/homepage-cms'), url('/admin/footer-settings')]))),
            $link('Good to Know', 'icon-book.svg', $href(BlogPostResource::class), $current(array_filter([$href(BlogPostResource::class)]))),
        ])],
        ['label' => 'Marketing', 'items' => array_filter([
            $link('Newsletter', 'icon-newsletter.svg', $firstUrl($newsletterUrls), $current($newsletterUrls)),
            $link('Email', 'icon-email.svg', $firstUrl($emailUrls), $current($emailUrls)),
            $link('Analytics', 'icon-analytics.svg', $firstUrl($analyticsUrls), $current($analyticsUrls)),
        ])],
    ];

    $settings = $link(
        'Settings',
        'icon-settings.svg',
        $firstUrl($settingsUrls),
        $current(array_filter([...$settingsUrls, url('/admin/moderation-settings')])),
    );
@endphp

<script>document.documentElement.classList.add('tb-redesign')</script>

<aside class="tb-rail" aria-label="Admin">
  <a class="tb-rail-logo" href="{{ $href(Dashboard::class) ?? url('/admin') }}">
    <img src="{{ $icon('logo.svg') }}" alt="MyTerraBook" width="160" height="15">
  </a>
  <nav class="tb-rail-nav">
    @foreach ($groups as $group)
      @if (count($group['items']))
        @if ($group['label'])
          <p class="tb-rail-group">{{ $group['label'] }}</p>
        @endif
        @foreach ($group['items'] as $item)
          <a href="{{ $item['url'] }}" @class(['tb-rail-link', 'is-active' => $item['active']])>
            <span class="tb-rail-icon"><img src="{{ $icon($item['icon']) }}" alt="" width="16" height="16"></span>
            <span class="tb-rail-label">{{ $item['label'] }}</span>
            @if ($item['badge'])
              <span class="tb-rail-badge">{{ $item['badge'] }}</span>
            @endif
          </a>
        @endforeach
      @endif
    @endforeach
  </nav>
  @if ($settings)
    <a href="{{ $settings['url'] }}" @class(['tb-rail-link', 'tb-rail-settings', 'is-active' => $settings['active']])>
      <span class="tb-rail-icon"><img src="{{ $icon($settings['icon']) }}" alt="" width="16" height="16"></span>
      <span class="tb-rail-label">{{ $settings['label'] }}</span>
    </a>
  @endif
  <div class="tb-rail-user">
    <span class="tb-rail-avatar">{{ $initials }}</span>
    <span class="tb-rail-user-copy">
      <strong>{{ $name }}</strong>
      <small>{{ $roleLabel }}</small>
      @if ($email !== '')
        <small>{{ $email }}</small>
      @endif
    </span>
    <details class="tb-rail-more">
      <summary aria-label="Account menu"><img src="{{ $icon('icon-more.svg') }}" alt="" width="16" height="16"></summary>
      <div>
        @if ($profile = filament()->getProfileUrl())
          <a href="{{ $profile }}">Profile</a>
        @endif
        <form method="post" action="{{ filament()->getLogoutUrl() }}">
          @csrf
          <button type="submit">Sign out</button>
        </form>
      </div>
    </details>
  </div>
</aside>

@if ($comingSoon)
  <div class="tb-coming-soon">Coming Soon mode is on</div>
@endif

<style>
  html.tb-redesign {
    --tb-navy: #041e37;
    --tb-navy-700: #0b2c4d;
    --tb-steel: #4474a4;
    --tb-steel-50: #e8eff7;
    --tb-page: #f3f6fa;
    --tb-surface: #ffffff;
    --tb-subtle: #f7f9fc;
    --tb-border: #dbe3ec;
    --tb-border-soft: #eaf0f5;
    --tb-text: #0d1b2a;
    --tb-text-secondary: #5b6b7d;
    --tb-text-tertiary: #8a99a9;
    --tb-ok: #1f7a4d;
    --tb-ok-bg: #e3f3ea;
    --tb-warn: #9a5b00;
    --tb-warn-bg: #fdf1dc;
    --tb-bad: #b42318;
    --tb-bad-bg: #fde7e5;
    --tb-amber: #e8a33a;
  }
  html.tb-redesign body,
  html.tb-redesign .fi-body {
    background: var(--tb-page);
    color: var(--tb-text);
    font-family: "IBM Plex Sans", sans-serif;
  }
  html.tb-redesign .fi-sidebar.fi-main-sidebar,
  html.tb-redesign .fi-sidebar-close-overlay,
  html.tb-redesign .fi-topbar-open-sidebar-btn,
  html.tb-redesign .fi-topbar-close-sidebar-btn,
  html.tb-redesign .fi-topbar-collapse-sidebar-btn-ctn,
  html.tb-redesign .fi-topbar-open-collapse-sidebar-btn,
  html.tb-redesign .fi-topbar-close-collapse-sidebar-btn,
  html.tb-redesign .ir-rail,
  html.tb-redesign .ir-rail-mobile {
    display: none !important;
  }
  html.tb-redesign .fi-topbar-ctn,
  html.tb-redesign .fi-layout,
  html.tb-redesign.ir-shell .fi-topbar-ctn,
  html.tb-redesign.ir-shell .fi-layout {
    padding-left: 248px !important;
  }
  html.tb-redesign .fi-main,
  html.tb-redesign .fi-page {
    background: var(--tb-page);
  }
  html.tb-redesign .fi-topbar {
    min-height: 63px;
    background: var(--tb-surface);
    border-bottom: 1px solid var(--tb-border);
    box-shadow: none;
  }
  html.tb-redesign .fi-header-heading,
  html.tb-redesign .fi-simple-header-heading,
  html.tb-redesign .fi-ta-header-heading {
    font-family: Manrope, sans-serif;
    color: var(--tb-text);
    letter-spacing: -0.4px;
  }
  html.tb-redesign .fi-btn.fi-color-primary {
    background: var(--tb-navy);
    border-color: var(--tb-navy);
  }
  html.tb-redesign .fi-section,
  html.tb-redesign .fi-ta-ctn,
  html.tb-redesign .fi-wi-stats-overview-stat {
    border: 1px solid var(--tb-border);
    border-radius: 12px;
    background: var(--tb-surface);
    box-shadow: none;
  }
  html.tb-redesign .fi-badge {
    font-family: "IBM Plex Sans", sans-serif;
    font-weight: 600;
  }
  .tb-rail {
    position: fixed;
    z-index: 40;
    top: 0;
    left: 0;
    display: flex;
    flex-direction: column;
    width: 248px;
    height: 100dvh;
    padding: 14px;
    background: #041e37;
    color: #dbe5f2;
  }
  .tb-rail-logo {
    display: flex;
    align-items: center;
    height: 36px;
    margin: 0 4px 14px;
  }
  .tb-rail-logo img { display: block; width: 148px; height: auto; }
  .tb-rail-nav {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 2px;
    min-height: 0;
    overflow: auto;
  }
  .tb-rail-group {
    margin: 14px 8px 4px;
    font-family: "IBM Plex Mono", ui-monospace, monospace;
    font-size: 10.5px;
    font-weight: 600;
    line-height: 14px;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #738fad;
  }
  .tb-rail-settings { margin-top: 8px; }
  .tb-rail-link {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 36px;
    padding: 3px 8px 3px 4px;
    border-radius: 9px;
    color: #dbe5f2;
    font-family: "IBM Plex Sans", sans-serif;
    font-size: 13.5px;
    font-weight: 500;
    line-height: 18px;
    text-decoration: none;
  }
  .tb-rail-link:hover { background: #0b2c4d; }
  .tb-rail-link.is-active { background: #0b2c4d; color: #fff; }
  .tb-rail-icon {
    display: grid;
    flex: 0 0 28px;
    place-items: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #0b2c4d;
  }
  .tb-rail-link.is-active .tb-rail-icon { background: #041e37; }
  .tb-rail-icon img { display: block; width: 16px; height: 16px; }
  .tb-rail-label { flex: 1; min-width: 0; }
  .tb-rail-badge {
    min-width: 18px;
    padding: 0 6px;
    border-radius: 999px;
    background: #e8a33a;
    color: #041e37;
    font-size: 11px;
    font-weight: 700;
    line-height: 18px;
    text-align: center;
  }
  .tb-rail-user {
    display: grid;
    grid-template-columns: 32px minmax(0, 1fr) 28px;
    gap: 8px;
    align-items: center;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #0b2c4d;
  }
  .tb-rail-avatar {
    display: grid;
    width: 32px;
    height: 32px;
    place-items: center;
    border-radius: 999px;
    background: #4474a4;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
  }
  .tb-rail-user-copy { display: flex; flex-direction: column; min-width: 0; }
  .tb-rail-user-copy strong,
  .tb-rail-user-copy small {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .tb-rail-user-copy strong { color: #fff; font-size: 13px; font-weight: 600; }
  .tb-rail-user-copy small { color: #8fa7c0; font-size: 11px; }
  .tb-rail-more { position: relative; }
  .tb-rail-more summary {
    display: grid;
    width: 28px;
    height: 28px;
    place-items: center;
    border-radius: 8px;
    list-style: none;
    cursor: pointer;
  }
  .tb-rail-more summary::-webkit-details-marker { display: none; }
  .tb-rail-more summary:hover { background: #0b2c4d; }
  .tb-rail-more div {
    position: absolute;
    right: 0;
    bottom: 36px;
    display: flex;
    flex-direction: column;
    min-width: 140px;
    padding: 6px;
    border: 1px solid #dbe3ec;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 8px 24px rgb(4 30 55 / 16%);
  }
  .tb-rail-more a,
  .tb-rail-more button {
    padding: 8px 10px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #0d1b2a;
    font: 500 13px/18px "IBM Plex Sans", sans-serif;
    text-align: left;
    text-decoration: none;
    cursor: pointer;
  }
  .tb-rail-more a:hover,
  .tb-rail-more button:hover { background: #f7f9fc; }
  .tb-coming-soon {
    position: fixed;
    z-index: 30;
    top: 18px;
    left: 50%;
    padding: 4px 10px;
    border-radius: 999px;
    background: #fdf1dc;
    color: #9a5b00;
    font-family: "IBM Plex Sans", sans-serif;
    font-size: 12px;
    font-weight: 600;
    line-height: 16px;
    transform: translateX(-50%);
  }
  @media (max-width: 720px) {
    .tb-rail { width: 72px; padding: 10px; }
    .tb-rail-logo, .tb-rail-label, .tb-rail-badge, .tb-rail-user-copy, .tb-rail-group { display: none; }
    html.tb-redesign .fi-topbar-ctn,
    html.tb-redesign .fi-layout,
    html.tb-redesign.ir-shell .fi-topbar-ctn,
    html.tb-redesign.ir-shell .fi-layout { padding-left: 72px !important; }
  }
</style>
