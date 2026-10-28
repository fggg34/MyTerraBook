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

@php
    $mobileScreen = \App\Support\AdminScreen::current();
    $mobileTitle = $mobileScreen['title'] ?? null;
    if (! is_string($mobileTitle) || $mobileTitle === '') {
        $mobileTitle = 'Dashboard';
    }
    $homeUrl = $href(Dashboard::class) ?? url('/admin');
    $bookingsUrl = $href(OrderResource::class);
    $calendarUrl = $firstUrl($calendarUrls);
    $catalogUrl = $firstUrl($guestHouseUrls) ?? $firstUrl($vehicleUrls);
    $tabHome = $current(array_filter([$homeUrl]), true);
    $tabBookings = $current(array_filter([$bookingsUrl, $href(GuestHouseBookingResource::class), $href(BookingChangeRequestResource::class)]));
    $tabCalendar = $current($calendarUrls);
    $tabCatalog = $current(array_merge($guestHouseUrls, $vehicleUrls, $pricingUrls));
@endphp

<header class="tb-mbar">
    <button type="button" class="tb-mbar-btn" data-tb-more aria-label="Menu">
        <img src="{{ $icon('icon-m-menu.svg') }}" alt="" width="22" height="22">
    </button>
    <h1>{{ $mobileTitle }}</h1>
    <button type="button" class="tb-mbar-btn" data-tb-search aria-label="Search">
        <img src="{{ $icon('icon-m-search.svg') }}" alt="" width="20" height="20">
    </button>
    <span class="tb-mbar-bell"><img src="{{ $icon('icon-m-bell.svg') }}" alt="" width="40" height="40"></span>
    <span class="tb-mbar-avatar">{{ $initials }}</span>
</header>

<nav class="tb-mtabs" aria-label="Admin">
    <a href="{{ $homeUrl }}" @class(['is-active' => $tabHome])>
        <span><img src="{{ $icon('icon-m-home.svg') }}" alt="" width="20" height="20"></span>
        Home
    </a>
    @if ($bookingsUrl)
        <a href="{{ $bookingsUrl }}" @class(['is-active' => $tabBookings])>
            <span>
                <img src="{{ $icon('icon-m-bookings.svg') }}" alt="" width="20" height="20">
                @if ($pendingReservations)
                    <i>{{ $pendingReservations }}</i>
                @endif
            </span>
            Bookings
        </a>
    @endif
    @if ($calendarUrl)
        <a href="{{ $calendarUrl }}" @class(['is-active' => $tabCalendar])>
            <span><img src="{{ $icon('icon-m-calendar.svg') }}" alt="" width="20" height="20"></span>
            Calendar
        </a>
    @endif
    @if ($catalogUrl)
        <a href="{{ $catalogUrl }}" @class(['is-active' => $tabCatalog])>
            <span><img src="{{ $icon('icon-m-catalog.svg') }}" alt="" width="20" height="20"></span>
            Catalog
        </a>
    @endif
    <button type="button" data-tb-more>
        <span><img src="{{ $icon('icon-m-more.svg') }}" alt="" width="20" height="20"></span>
        More
    </button>
</nav>

<div class="tb-mmore" hidden>
    <div class="tb-mmore-sheet">
        <header>
            <strong>More</strong>
            <button type="button" data-tb-more-close aria-label="Close">Close</button>
        </header>
        @foreach ($groups as $group)
            @if (count($group['items']))
                @if ($group['label'])
                    <p>{{ $group['label'] }}</p>
                @endif
                @foreach ($group['items'] as $item)
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @endforeach
            @endif
        @endforeach
        @if ($settings)
            <p>Settings</p>
            <a href="{{ $settings['url'] }}">{{ $settings['label'] }}</a>
        @endif
    </div>
</div>

<script>
    document.querySelectorAll('[data-tb-more]').forEach((button) => {
        button.addEventListener('click', () => {
            const sheet = document.querySelector('.tb-mmore');
            if (sheet) sheet.hidden = !sheet.hidden;
        });
    });
    document.querySelector('[data-tb-more-close]')?.addEventListener('click', () => {
        const sheet = document.querySelector('.tb-mmore');
        if (sheet) sheet.hidden = true;
    });
    document.querySelector('[data-tb-search]')?.addEventListener('click', () => {
        const input = document.querySelector('.fi-global-search-field input');
        document.documentElement.classList.toggle('tb-mobile-search');
        input?.focus();
    });
</script>


<link rel="stylesheet" href="{{ asset('admin-redesign/admin-list.css') }}">
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
  html.tb-redesign .fi-main.fi-width-7xl,
  html.tb-redesign .fi-main.fi-width-full,
  html.tb-redesign .fi-page {
    background: var(--tb-page);
    max-width: none !important;
    width: 100%;
    margin-inline: 0;
  }
  html.tb-redesign .fi-main,
  html.tb-redesign .fi-main.fi-width-7xl,
  html.tb-redesign .fi-main.fi-width-full {
    padding: 24px 32px 40px;
  }
  html.tb-redesign .fi-page-content,
  html.tb-redesign .fi-page-content .fi-sc {
    max-width: none;
    width: 100%;
  }
  html.tb-redesign .fi-page:has(.tb-dash) .fi-header,
  html.tb-redesign .fi-page:has(.tb-dash) .fi-breadcrumbs {
    display: none;
  }
  html.tb-redesign .fi-page:has(.tb-dash) .fi-page-content,
  html.tb-redesign .fi-page:has(.tb-dash) .fi-wi {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  html.tb-redesign .fi-wi-widget {
    background: transparent;
    border: 0;
    box-shadow: none;
  }
  html.tb-redesign .fi-topbar {
    padding-inline: 32px;
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
  html.tb-redesign .fi-topbar-start,
  html.tb-redesign .fi-user-menu,
  html.tb-redesign .fi-logo { display: none !important; }
  html.tb-redesign .fi-topbar-end {
    display: flex;
    flex: 1;
    align-items: center;
    gap: 12px;
    margin-inline-start: 0;
  }
  html.tb-redesign .fi-global-search-ctn { width: 440px; max-width: 46vw; margin-right: auto; }
  html.tb-redesign .fi-global-search-field .fi-input-wrp {
    height: 36px;
    border: 0;
    border-radius: 8px;
    background: #f3f6fa;
    box-shadow: none;
  }
  html.tb-redesign .fi-global-search-field .fi-input { font-size: 14px; }
  html.tb-redesign .fi-global-search-field .fi-input-wrp-suffix { font-family: "IBM Plex Mono", ui-monospace, monospace; font-size: 12px; }
  .tb-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px 5px 10px;
    border-radius: 999px;
    background: #fdf1dc;
    color: #9a5b00;
    font-family: "IBM Plex Sans", sans-serif;
    font-size: 12px;
    font-weight: 600;
    line-height: 16px;
    white-space: nowrap;
  }
  .tb-status img { width: 7px; height: 7px; }
  .tb-new { position: relative; }
  .tb-new summary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 16px;
    border-radius: 6px;
    background: #041e37;
    color: #fff;
    font-family: "IBM Plex Sans", sans-serif;
    font-size: 14px;
    font-weight: 600;
    line-height: 20px;
    list-style: none;
    cursor: pointer;
  }
  .tb-new summary::-webkit-details-marker { display: none; }
  .tb-new summary img { width: 16px; height: 16px; }
  .tb-new div {
    position: absolute;
    right: 0;
    z-index: 50;
    display: flex;
    flex-direction: column;
    min-width: 200px;
    margin-top: 6px;
    padding: 6px;
    border: 1px solid #dbe3ec;
    border-radius: 10px;
    background: #fff;
    box-shadow: 0 8px 24px rgb(4 30 55 / 12%);
  }
  .tb-new a {
    padding: 8px 10px;
    border-radius: 8px;
    color: #0d1b2a;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
  }
  .tb-new a:hover { background: #f7f9fc; }
  .tb-bell { display: grid; width: 36px; height: 36px; place-items: center; }
  .tb-bell img { width: 36px; height: 36px; }
  .tb-crumb {
    margin: 0 0 4px;
    font-family: "IBM Plex Mono", ui-monospace, monospace;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: #8a99a9;
  }
  .tb-title {
    margin: 0 0 6px;
    font-family: Manrope, sans-serif;
    font-size: 28px;
    font-weight: 800;
    line-height: 36px;
    letter-spacing: -0.5px;
    color: #0d1b2a;
  }
  .tb-desc { max-width: 46rem; margin: 0 0 14px; font-size: 14px; line-height: 20px; color: #5b6b7d; }
  .fi-page:has(.tb-lead[data-replace-title]) .fi-header-heading { display: none; }
  .tb-subnav {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
    margin: 0 0 16px;
  }
  .tb-subnav-group {
    margin-left: 8px;
    font-family: "IBM Plex Mono", ui-monospace, monospace;
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #8a99a9;
  }
  .tb-subnav a {
    padding: 6px 10px;
    border-radius: 8px;
    color: #0d1b2a;
    font-size: 14px;
    font-weight: 500;
    line-height: 20px;
    text-decoration: none;
  }
  .tb-subnav a:hover { background: #f7f9fc; }
  .tb-subnav a.is-active { background: #e8eff7; color: #041e37; font-weight: 600; }
  html.tb-redesign .fi-page:has(.tb-lead-aside) {
    display: grid;
    grid-template-columns: 220px minmax(0, 1fr);
    column-gap: 28px;
    align-items: start;
  }
  html.tb-redesign .fi-page:has(.tb-lead-aside) > .tb-lead-aside { display: contents; }
  html.tb-redesign .fi-page:has(.tb-lead-aside) > * { grid-column: 2; }
  html.tb-redesign .fi-page:has(.tb-lead-aside) .tb-subnav {
    grid-column: 1;
    grid-row: 1 / span 40;
    flex-direction: column;
    align-items: stretch;
    gap: 2px;
    margin: 0;
    position: sticky;
    top: 24px;
  }
  html.tb-redesign .fi-page:has(.tb-lead-aside) .tb-subnav-group {
    flex-basis: auto;
    margin: 14px 0 4px 10px;
    font-size: 10px;
  }
  html.tb-redesign .fi-page:has(.tb-lead-aside) .tb-subnav-group:first-child { margin-top: 0; }
  html.tb-redesign .fi-page:has(.tb-lead-aside) .tb-subnav a {
    padding: 8px 10px;
  }
  html.tb-redesign .fi-page:has(.tb-lead-aside) .tb-lead { padding-right: 0; }
  html.tb-redesign .fi-page:has(.tb-lead-aside) .fi-header {
    margin-top: -4px;
  }
  html.tb-redesign .ir-global-tabs { display: none; }
  html.tb-redesign .ir-global-card {
    border-color: #dbe3ec;
    border-radius: 12px;
  }
  html.tb-redesign .ir-global-card__title {
    font-family: Manrope, sans-serif;
    font-size: 16px;
    font-weight: 800;
    color: #0d1b2a;
  }
  html.tb-redesign .fi-header-heading {
    font-family: Manrope, sans-serif;
    font-size: 28px;
    font-weight: 800;
    line-height: 36px;
    letter-spacing: -0.5px;
  }
  html.tb-redesign .fi-page:has(.tb-lead) .tb-lead { padding-right: min(42vw, 360px); }
  html.tb-redesign .fi-page:has(.tb-lead) .fi-header {
    display: flex;
    justify-content: flex-end;
    align-items: flex-start;
    margin: -92px 0 18px;
    min-height: 0;
  }
  html.tb-redesign .fi-page:has(.tb-lead) .fi-header-heading,
  html.tb-redesign .fi-page:has(.tb-lead) .fi-header-subheading,
  html.tb-redesign .fi-page:has(.tb-lead) .fi-breadcrumbs {
    display: none;
  }
  html.tb-redesign .fi-page:has(.tb-dash) .fi-header { display: none; margin: 0; }
  html.tb-redesign .fi-btn {
    height: 38px;
    border-radius: 6px;
    font-family: "IBM Plex Sans", sans-serif;
    font-size: 14px;
    font-weight: 600;
    box-shadow: none;
  }
  html.tb-redesign .fi-btn.fi-color-primary,
  html.tb-redesign .fi-btn.fi-color-success {
    background: var(--tb-navy);
    border-color: var(--tb-navy);
    color: #fff;
  }
  html.tb-redesign .fi-btn.fi-outlined,
  html.tb-redesign .fi-btn.fi-color-gray {
    background: #fff;
    border: 1px solid var(--tb-border);
    color: var(--tb-text);
  }
  html.tb-redesign .fi-tabs {
    gap: 4px;
    background: transparent;
    border: 0;
    box-shadow: none;
  }
  html.tb-redesign .fi-tabs-item {
    min-height: 32px;
    padding: 6px 10px;
    border-radius: 8px;
    color: var(--tb-text-secondary);
    font-size: 14px;
    font-weight: 500;
  }
  html.tb-redesign .fi-tabs-item.fi-active {
    background: var(--tb-steel-50);
    color: var(--tb-navy);
    font-weight: 600;
    box-shadow: none;
  }
  html.tb-redesign .fi-tabs-item .fi-badge {
    background: transparent;
    color: inherit;
    font-weight: 700;
  }
  html.tb-redesign .fi-ta-ctn {
    overflow: hidden;
    border: 1px solid var(--tb-border);
    border-radius: 12px;
    background: var(--tb-surface);
    box-shadow: none;
  }
  html.tb-redesign .fi-ta-header-ctn,
  html.tb-redesign .fi-ta-header {
    background: var(--tb-surface);
    border-color: var(--tb-border-soft);
  }
  html.tb-redesign .fi-ta-header-toolbar {
    gap: 8px;
    padding: 12px 14px;
  }
  html.tb-redesign .fi-ta-search-field .fi-input-wrp {
    min-width: 220px;
    height: 36px;
    border-radius: 8px;
    background: #fff;
  }
  html.tb-redesign .fi-ta-header-cell {
    background: var(--tb-subtle);
    color: var(--tb-text-secondary);
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0;
  }
  html.tb-redesign .fi-ta-row {
    border-color: var(--tb-border-soft);
  }
  html.tb-redesign .fi-ta-cell {
    color: var(--tb-text);
    font-size: 14px;
    line-height: 20px;
  }
  html.tb-redesign .fi-ta-cell .fi-ta-text-description {
    color: var(--tb-text-secondary);
    font-size: 12px;
  }
  html.tb-redesign .fi-ta-content-grid {
    gap: 16px;
    padding: 16px;
    background: transparent;
  }
  html.tb-redesign .fi-ta-content-grid .fi-ta-record {
    overflow: hidden;
    border: 1px solid var(--tb-border);
    border-radius: 12px;
    background: var(--tb-surface);
    box-shadow: none;
  }
  html.tb-redesign .fi-ta-content-grid .fi-ta-cell {
    padding: 0;
  }
  html.tb-redesign .fi-ta-content-grid .fi-ta-actions {
    padding: 8px 12px 12px;
  }
  html.tb-redesign .fi-pagination {
    padding: 12px 14px;
    border-top: 1px solid var(--tb-border-soft);
    color: var(--tb-text-secondary);
    font-size: 13px;
  }
  html.tb-redesign .fi-input-wrp {
    border: 1px solid var(--tb-border);
    border-radius: 8px;
    background: #fff;
    box-shadow: none;
  }
  html.tb-redesign .fi-badge {
    gap: 6px;
    padding: 3px 10px 3px 8px;
    border: 0;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    line-height: 16px;
    box-shadow: none;
  }
  html.tb-redesign .fi-badge.fi-color-success { background: var(--tb-ok-bg); color: var(--tb-ok); }
  html.tb-redesign .fi-badge.fi-color-warning { background: var(--tb-warn-bg); color: var(--tb-warn); }
  html.tb-redesign .fi-badge.fi-color-danger { background: var(--tb-bad-bg); color: var(--tb-bad); }
  html.tb-redesign .fi-badge.fi-color-gray { background: var(--tb-subtle); color: var(--tb-text-secondary); }
  html.tb-redesign .fi-badge.fi-color-info,
  html.tb-redesign .fi-badge.fi-color-primary { background: var(--tb-steel-50); color: var(--tb-steel); }
  html.tb-redesign .fi-section,
  html.tb-redesign .fi-sc-section {
    overflow: hidden;
    border: 1px solid var(--tb-border);
    border-radius: 12px;
    background: var(--tb-surface);
    box-shadow: none;
  }
  html.tb-redesign .fi-section-header,
  html.tb-redesign .fi-sc-section-header {
    background: var(--tb-subtle);
    border-color: var(--tb-border-soft);
  }
  html.tb-redesign .fi-fo-field-label,
  html.tb-redesign .fi-fo-field-wrp-label {
    color: var(--tb-text);
    font-size: 13px;
    font-weight: 600;
  }
  html.tb-redesign .ir-global-tab[data-active="true"] { background: #041e37; border-color: #041e37; }
  html.tb-redesign .ir-global-card { border-color: #dbe3ec; border-radius: 12px; }
  html.tb-redesign .ir-summary {
    border-color: var(--tb-border);
    border-radius: 12px;
    background: var(--tb-surface);
    box-shadow: none;
  }
  html.tb-redesign .ir-summary p { color: var(--tb-text-tertiary); }
  html.tb-redesign .ir-summary strong { color: var(--tb-text); }
  html.tb-redesign .ir-detail-tab {
    border-bottom-color: var(--tb-navy);
    color: var(--tb-navy);
  }
  .tb-vehicle-card { display: flex; flex-direction: column; min-height: 100%; }
  .tb-vehicle-photo {
    display: grid;
    height: 148px;
    place-items: center;
    background: linear-gradient(180deg, #e8eff7 0%, #f3f6fa 100%);
  }
  .tb-vehicle-photo img { width: 100%; height: 148px; object-fit: cover; }
  .tb-vehicle-photo svg { width: 48px; height: 48px; color: #8fb0d0; }
  .tb-vehicle-body { display: flex; flex: 1; flex-direction: column; gap: 8px; padding: 12px 14px 14px; }
  .tb-vehicle-pills { display: flex; flex-wrap: wrap; gap: 6px; }
  .tb-vehicle-name { margin: 0; font-size: 15px; font-weight: 700; line-height: 20px; color: var(--tb-text); }
  .tb-vehicle-meta { margin: 0; color: var(--tb-text-secondary); font-size: 12px; line-height: 16px; }
  .tb-vehicle-foot {
    display: flex;
    justify-content: space-between;
    gap: 8px;
    margin-top: auto;
    padding-top: 10px;
    border-top: 1px solid var(--tb-border-soft);
  }
  .tb-vehicle-foot strong { display: block; font-size: 13px; font-weight: 600; color: var(--tb-text); }
  .tb-vehicle-foot small { color: var(--tb-text-secondary); font-size: 12px; }
  .tb-vehicle-foot .is-missing { color: #e07a00; font-weight: 700; }
  html.tb-redesign .tb-hidden-action { display: none !important; }
  html.tb-redesign .fi-page:has(.tb-edit-head) .tb-lead,
  html.tb-redesign .fi-page:has(.tb-edit-head) .fi-breadcrumbs { display: none; }
  html.tb-redesign .tb-edit-head { display: grid; gap: 10px; margin: 0 0 16px; }
  html.tb-redesign .tb-edit-back {
    width: fit-content;
    color: var(--tb-steel);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
  }
  html.tb-redesign .tb-edit-back:before { content: "← "; }
  html.tb-redesign .tb-edit-head-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
  }
  html.tb-redesign .tb-edit-pills { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
  html.tb-redesign .tb-edit-meta { margin: 6px 0 0; color: var(--tb-text-secondary); font-size: 13px; }
  html.tb-redesign .tb-edit-head-actions .fi-header,
  html.tb-redesign .tb-edit-head-actions .fi-header-heading,
  html.tb-redesign .tb-edit-head-actions .fi-header-subheading {
    margin: 0;
    padding: 0;
    background: transparent;
    border: 0;
    min-height: 0;
  }
  html.tb-redesign .tb-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 3px 8px;
    font-size: 11px;
    font-weight: 700;
    line-height: 14px;
    background: var(--tb-subtle);
    color: var(--tb-text-secondary);
  }
  html.tb-redesign .tb-pill--ok { background: var(--tb-ok-bg); color: var(--tb-ok); }
  html.tb-redesign .tb-pill--warn { background: var(--tb-warn-bg); color: var(--tb-warn); }
  html.tb-redesign .tb-pill--neutral { background: #eef3f8; color: #5b6b7d; }
  html.tb-redesign .tb-summary {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
    margin: 0 0 20px;
    padding: 14px 16px;
    border: 1px solid var(--tb-border);
    border-radius: 12px;
    background: var(--tb-surface);
  }
  html.tb-redesign .tb-summary-item { display: grid; gap: 2px; min-width: 0; }
  html.tb-redesign .tb-summary-item span { color: var(--tb-text-tertiary); font-size: 11px; font-weight: 700; letter-spacing: 0.4px; text-transform: uppercase; }
  html.tb-redesign .tb-summary-item strong { color: var(--tb-text); font-size: 14px; font-weight: 700; }
  html.tb-redesign .tb-summary-item small { color: var(--tb-text-secondary); font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  html.tb-redesign .fi-page:has(.tb-form-toc) {
    display: grid;
    grid-template-columns: 176px minmax(0, 1fr);
    column-gap: 28px;
    align-items: start;
  }
  html.tb-redesign .fi-page:has(.tb-form-toc) .fi-header { display: contents; }
  html.tb-redesign .fi-page:has(.tb-form-toc) .tb-edit-head,
  html.tb-redesign .fi-page:has(.tb-form-toc) .tb-summary { grid-column: 1 / -1; }
  html.tb-redesign .tb-form-toc {
    display: grid;
    gap: 4px;
    position: sticky;
    top: 88px;
    padding: 8px 0;
  }
  html.tb-redesign .tb-form-toc a {
    display: block;
    padding: 8px 10px;
    border-radius: 8px;
    color: var(--tb-text-secondary);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
  }
  html.tb-redesign .tb-form-toc a:hover,
  html.tb-redesign .tb-form-toc a:focus { background: #e8eef6; color: var(--tb-text); }
  html.tb-redesign .fi-page:has(.tb-form-toc) .fi-page-content,
  html.tb-redesign .fi-page:has(.tb-form-toc) .fi-form { grid-column: 2; }
  html.tb-redesign .tb-totals {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
    margin-top: 8px;
    padding: 12px 14px;
    border: 1px solid var(--tb-border);
    border-radius: 10px;
    background: #f7fafc;
  }
  html.tb-redesign .tb-totals div { display: grid; gap: 2px; }
  html.tb-redesign .tb-totals span { color: var(--tb-text-tertiary); font-size: 11px; font-weight: 700; }
  html.tb-redesign .tb-totals strong { color: var(--tb-text); font-size: 14px; font-weight: 700; font-variant-numeric: tabular-nums; }
  html.tb-redesign .tb-totals-due strong { color: #c05621; }
  html.tb-redesign .tb-side-card {
    padding: 14px 16px;
    border: 1px solid var(--tb-border);
    border-radius: 12px;
    background: var(--tb-surface);
  }
  html.tb-redesign .tb-side-card h2 { margin: 0 0 10px; font-size: 15px; font-weight: 700; color: var(--tb-text); }
  html.tb-redesign .tb-side-muted { margin: 0; color: var(--tb-text-secondary); font-size: 13px; }
  html.tb-redesign .tb-activity { margin: 0; padding: 0; list-style: none; display: grid; gap: 10px; }
  html.tb-redesign .tb-activity li { display: grid; gap: 2px; padding-left: 14px; border-left: 2px solid #dbe3ec; }
  html.tb-redesign .tb-activity strong { font-size: 13px; color: var(--tb-text); }
  html.tb-redesign .tb-activity span { font-size: 12px; color: var(--tb-text-secondary); }
  html.tb-redesign .tb-side-card--danger { border-color: #f3c4c4; }
  html.tb-redesign .tb-side-card--danger h2 { color: #b42318; }
  html.tb-redesign .tb-danger-btn {
    margin-top: 10px;
    border: 1px solid #f3c4c4;
    border-radius: 8px;
    background: #fff5f5;
    color: #b42318;
    font-size: 13px;
    font-weight: 700;
    padding: 8px 12px;
    cursor: pointer;
  }
  html.tb-redesign .tb-inline-danger { margin-top: 16px; }
  html.tb-redesign .tb-calendar-open {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 36px;
    border: 1px solid var(--tb-border);
    border-radius: 8px;
    background: #fff;
    color: var(--tb-text);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
  }
  html.tb-redesign .fi-page:has(.tb-amenity-board) .fi-ta,
  html.tb-redesign .fi-page:has(.tb-email-board) .fi-ta,
  html.tb-redesign .fi-page:has(.tb-dual-tables) .fi-ta { display: none !important; }
  html.tb-redesign .tb-amenity-board {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
  }
  html.tb-redesign .tb-amenity-group,
  html.tb-redesign .tb-email-group {
    border: 1px solid var(--tb-border);
    border-radius: 12px;
    background: var(--tb-surface);
    overflow: hidden;
  }
  html.tb-redesign .tb-amenity-group header,
  html.tb-redesign .tb-email-group header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 12px 14px 8px;
  }
  html.tb-redesign .tb-amenity-group h2,
  html.tb-redesign .tb-email-group h2 { margin: 0; font-size: 14px; font-weight: 700; color: var(--tb-text); }
  html.tb-redesign .tb-amenity-group header span,
  html.tb-redesign .tb-email-group header span { color: var(--tb-text-secondary); font-size: 12px; font-weight: 600; }
  html.tb-redesign .tb-amenity-group ul,
  html.tb-redesign .tb-email-group ul { margin: 0; padding: 0 8px 10px; list-style: none; }
  html.tb-redesign .tb-amenity-group li,
  html.tb-redesign .tb-email-group li {
    display: grid;
    grid-template-columns: 28px minmax(0, 1fr) auto;
    gap: 8px;
    align-items: center;
    min-height: 40px;
    padding: 4px 6px;
    border-radius: 8px;
  }
  html.tb-redesign .tb-email-group li { grid-template-columns: 28px minmax(0, 1fr) auto auto auto; }
  html.tb-redesign .tb-amenity-icon,
  html.tb-redesign .tb-email-mark {
    display: grid;
    place-items: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #e8eef6;
    color: #4474a4;
  }
  html.tb-redesign .tb-email-mark:before { content: ""; width: 14px; height: 10px; border: 1.5px solid #4474a4; border-radius: 2px; }
  html.tb-redesign .tb-amenity-name,
  html.tb-redesign .tb-email-copy strong { font-size: 13px; font-weight: 600; color: var(--tb-text); }
  html.tb-redesign .tb-email-copy p { margin: 0; color: var(--tb-text-secondary); font-size: 12px; }
  html.tb-redesign .tb-email-board { display: grid; gap: 16px; }
  html.tb-redesign .tb-icon-btn {
    border: 0;
    background: transparent;
    color: transparent;
    width: 28px;
    height: 28px;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    text-indent: -99px;
  }
  html.tb-redesign a.tb-icon-btn { display: inline-block; }
  html.tb-redesign .tb-icon-btn:after {
    content: "";
    position: absolute;
    inset: 7px;
    border-right: 1.5px solid #8a99a9;
    border-bottom: 1.5px solid #8a99a9;
    transform: rotate(-45deg);
    border-radius: 1px;
  }
  html.tb-redesign .tb-toggle {
    width: 36px;
    height: 20px;
    padding: 2px;
    border: 0;
    border-radius: 999px;
    background: #d5dee8;
    cursor: pointer;
  }
  html.tb-redesign .tb-toggle span {
    display: block;
    width: 16px;
    height: 16px;
    border-radius: 999px;
    background: #fff;
  }
  html.tb-redesign .tb-toggle.is-on { background: #041e37; }
  html.tb-redesign .tb-toggle.is-on span { margin-left: 16px; }
  html.tb-redesign .tb-dual-tables { display: grid; gap: 16px; }
  html.tb-redesign .tb-plain-table { width: 100%; border-collapse: collapse; }
  html.tb-redesign .tb-plain-table th {
    text-align: left;
    color: var(--tb-text-tertiary);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    padding: 8px 10px;
    border-bottom: 1px solid var(--tb-border);
  }
  html.tb-redesign .tb-plain-table td {
    padding: 10px;
    border-bottom: 1px solid var(--tb-border-soft);
    color: var(--tb-text);
    font-size: 13px;
  }
  html.tb-redesign .tb-dual-note {
    margin: 0;
    padding: 10px 12px;
    border-radius: 10px;
    background: #eef5fb;
    color: var(--tb-steel);
    font-size: 13px;
  }
  html.tb-redesign .tb-settings-stack { grid-template-columns: 1fr; }
  html.tb-redesign .tb-settings-toolbar { display: flex; justify-content: flex-end; margin-bottom: 12px; }
  html.tb-redesign .tb-settings-search { max-width: 240px; }
  html.tb-redesign .tb-toggle-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
    width: 100%;
  }
  html.tb-redesign .tb-toggle-row span { display: grid; gap: 2px; font-weight: 600; }
  html.tb-redesign .tb-toggle-row small { font-weight: 400; color: var(--tb-text-secondary); font-size: 12px; }
  html.tb-redesign .tb-toggle-row input[type="checkbox"] { margin-top: 4px; }
  html.tb-redesign .tb-btn-ghost {
    border: 1px solid var(--tb-border);
    background: #fff;
    color: var(--tb-text);
    border-radius: 8px;
    padding: 0.5rem 1rem;
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
  }
  html.tb-redesign .ir-global-actions { gap: 8px; }
  html.tb-redesign .ir-global-card__title { font-size: 16px; }
  @media (max-width: 1100px) {
    html.tb-redesign .tb-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    html.tb-redesign .tb-amenity-board { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    html.tb-redesign .fi-page:has(.tb-form-toc) { grid-template-columns: 1fr; }
    html.tb-redesign .tb-form-toc { position: static; display: flex; flex-wrap: wrap; gap: 4px; }
  }
  .tb-mbar, .tb-mtabs, .tb-mmore { display: none; }
  @media (max-width: 768px) {
    .tb-rail { display: none !important; }
    html.tb-redesign .fi-topbar-ctn,
    html.tb-redesign .fi-layout,
    html.tb-redesign.ir-shell .fi-topbar-ctn,
    html.tb-redesign.ir-shell .fi-layout { padding-left: 0 !important; }
    html.tb-redesign .fi-topbar { display: none; }
    html.tb-mobile-search .fi-topbar { display: flex; }
    html.tb-mobile-search .fi-global-search-ctn { width: 100%; max-width: none; }
    .tb-mbar {
      position: sticky;
      top: 0;
      z-index: 35;
      display: flex;
      align-items: center;
      gap: 4px;
      height: 56px;
      padding: 0 12px 0 8px;
      border-bottom: 1px solid #dbe3ec;
      background: #fff;
    }
    .tb-mbar h1 {
      flex: 1;
      margin: 0;
      font-family: Manrope, sans-serif;
      font-size: 18px;
      font-weight: 800;
      color: #041e37;
    }
    .tb-mbar-btn {
      display: grid;
      width: 44px;
      height: 44px;
      place-items: center;
      border: 0;
      border-radius: 12px;
      background: transparent;
    }
    .tb-mbar-bell { display: grid; width: 40px; height: 40px; place-items: center; }
    .tb-mbar-bell img { width: 40px; height: 40px; }
    .tb-mbar-avatar {
      display: grid;
      width: 32px;
      height: 32px;
      place-items: center;
      border-radius: 16px;
      background: #041e37;
      color: #fff;
      font-family: Manrope, sans-serif;
      font-size: 12px;
      font-weight: 700;
    }
    .tb-mtabs {
      position: fixed;
      right: 0;
      bottom: 0;
      left: 0;
      z-index: 40;
      display: flex;
      height: 84px;
      padding: 8px 6px 26px;
      border-top: 1px solid #dbe3ec;
      background: #fff;
    }
    .tb-mtabs a, .tb-mtabs button {
      display: flex;
      flex: 1;
      flex-direction: column;
      gap: 4px;
      align-items: center;
      padding-top: 4px;
      border: 0;
      background: transparent;
      color: #8a99a9;
      font-family: "IBM Plex Sans", sans-serif;
      font-size: 11px;
      font-weight: 500;
      text-decoration: none;
    }
    .tb-mtabs span {
      position: relative;
      display: grid;
      width: 52px;
      height: 30px;
      place-items: center;
      border-radius: 15px;
    }
    .tb-mtabs img { width: 20px; height: 20px; }
    .tb-mtabs i {
      position: absolute;
      top: -2px;
      left: 32px;
      display: grid;
      width: 16px;
      height: 16px;
      place-items: center;
      border-radius: 8px;
      background: #f4a83c;
      color: #041e37;
      font-family: Manrope, sans-serif;
      font-size: 10px;
      font-style: normal;
      font-weight: 700;
    }
    .tb-mtabs .is-active { color: #041e37; }
    .tb-mtabs .is-active span { background: #e8eff7; }
    html.tb-redesign .fi-main { padding-bottom: 96px; }
    .tb-mmore[hidden] { display: none; }
    .tb-mmore {
      position: fixed;
      inset: 0;
      z-index: 50;
      display: block;
      background: rgb(4 30 55 / 40%);
    }
    .tb-mmore-sheet {
      position: absolute;
      right: 0;
      bottom: 0;
      left: 0;
      max-height: 80dvh;
      overflow: auto;
      padding: 16px 16px 32px;
      border-radius: 16px 16px 0 0;
      background: #fff;
    }
    .tb-mmore-sheet header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
    .tb-mmore-sheet header button { border: 0; background: transparent; color: #4474a4; font-weight: 600; }
    .tb-mmore-sheet p {
      margin: 14px 0 4px;
      font-family: "IBM Plex Mono", ui-monospace, monospace;
      font-size: 10.5px;
      font-weight: 600;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #738fad;
    }
    .tb-mmore-sheet a { display: block; padding: 10px 0; color: #0d1b2a; font-size: 15px; font-weight: 500; text-decoration: none; }
  }
</style>
