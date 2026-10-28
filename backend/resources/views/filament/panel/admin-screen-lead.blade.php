@php
    $screen = \App\Support\AdminScreen::current();
    if ($screen && \App\Support\AdminScreen::on('admin/global-configuration')) {
        $screen['title'] = match (\App\Support\AdminScreen::query('tab', 'shop')) {
            'prices' => 'Prices & payments',
            'views' => 'Views & layout',
            'orders' => 'Orders & company',
            'texts' => 'Conditional texts',
            default => 'Shop & rentals',
        };
        $screen['description'] = match (\App\Support\AdminScreen::query('tab', 'shop')) {
            'prices' => 'How money is shown and charged.',
            'views' => 'How lists, maps and the public site look.',
            'orders' => 'Company details printed on bookings and invoices.',
            default => 'How booking works on the website: availability, search and system switches.',
        };
    }
    if ($screen && \App\Support\AdminScreen::on('admin/site-content', 'admin/site-content/*')) {
        $pageKey = (string) \App\Support\AdminScreen::query('tab', 'global');
        $screen['title'] = (string) (config('site_content.pages.'.$pageKey.'.label') ?: 'Site content');
        $screen['crumb'] = 'Website · Site content';
        $screen['description'] = match ($pageKey) {
            'global' => 'Logo, colors, header, footer and the other blocks already stored for every page.',
            'home' => 'Copy and media shown on the public homepage.',
            'terms' => 'Legal copy shown to customers at checkout and in the footer.',
            default => 'Copy and media shown on the public website.',
        };
    }
    $links = $screen ? \App\Support\AdminScreen::links($screen['nav']) : [];
    $group = null;
    $aside = is_array($screen) && in_array($screen['nav'] ?? null, ['settings', 'pricing'], true);
    $module = in_array($screen['nav'] ?? null, ['guest-houses', 'vehicles'], true);
    $guestHouseIndex = \App\Support\AdminScreen::on('admin/guest-houses/guest-houses');
    $vehicleIndex = \App\Support\AdminScreen::on('admin/impact-rent/cars');
    $headerActions = \App\Support\AdminScreen::headerActions();
    $bookingList = \App\Support\AdminScreen::on(
        'admin/impact-rent/orders',
        'admin/guest-houses/guest-house-bookings',
        'admin/booking-change-requests',
        'admin/impact-rent/orders-calendar',
        'admin/orders-calendar',
    );
    if ($screen && \App\Support\AdminScreen::on('admin/registered-hosts')) {
        $hostCount = \App\Models\User::query()->where('role', \App\Enums\UserRole::Host)->count();
        $screen['description'] = $hostCount.' '.($hostCount === 1 ? 'host and partner' : 'hosts and partners').'. Manage profiles, partner API and commission.';
    }
    if ($screen && \App\Support\AdminScreen::on('admin/registered-clients')) {
        $clientCount = \App\Models\User::query()->where('role', \App\Enums\UserRole::Customer)->count();
        $screen['description'] = $clientCount.' registered '.($clientCount === 1 ? 'client' : 'clients').' and their bookings.';
    }
    $pricingList = \App\Support\AdminScreen::on(
        'admin/impact-rent/special-prices',
        'admin/impact-rent/daily-fares',
        'admin/impact-rent/fares-overview',
        'admin/impact-rent/booking-restrictions',
        'admin/impact-rent/extra-hour-fares',
        'admin/impact-rent/hourly-fares',
        'admin/impact-rent/out-of-hours-fees',
        'admin/impact-rent/location-fees',
        'admin/impact-rent/rental-options',
        'admin/impact-rent/coupons',
        'admin/impact-rent/tax-rates',
        'admin/impact-rent/price-types',
    );
    $peopleList = \App\Support\AdminScreen::on(
        'admin/registered-hosts',
        'admin/registered-clients',
        'admin/listing-reviews',
        'admin/blog-posts',
        'admin/newsletter-subscribers',
        'admin/newsletter-campaigns',
    );
    $emailList = \App\Support\AdminScreen::on(
        'admin/email-templates',
        'admin/email-settings',
        'admin/email-testing',
        'admin/email-logs',
    );
    $settingsList = \App\Support\AdminScreen::on(
        'admin/payment-methods',
        'admin/custom-fields',
        'admin/users',
        'admin/logged-in-users',
        'admin/impact-rent/conditional-texts',
        'admin/impact-rent/backups',
    );
    $analyticsList = \App\Support\AdminScreen::on(
        'admin/reports',
        'admin/impact-rent/reports',
        'admin/tracking-statistics',
        'admin/impact-rent/tracking-statistics',
        'admin/impact-rent/tracking-campaigns',
    );
@endphp
@if ($screen && ($screen['crumb'] !== '' || $screen['title'] || $screen['description'] || $links !== []))
    <div @class(['tb-lead', 'tb-lead-settings' => $screen['settings'], 'tb-lead-aside' => $aside, 'tb-module' => $module, 'tb-gh-list' => $guestHouseIndex, 'tb-catalog-list' => $guestHouseIndex || $vehicleIndex || $bookingList || $pricingList || $peopleList || $emailList || $analyticsList || $settingsList || $headerActions !== []]) @if ($screen['title']) data-replace-title @endif>
        <div class="tb-lead-top">
            <div>
        @if ($screen['crumb'] !== '')
            <p class="tb-crumb">{{ $screen['crumb'] }}</p>
        @endif
        @if ($screen['title'])
            <h1 class="tb-title">{{ $screen['title'] }}</h1>
        @endif
        @if ($screen['description'])
            <p class="tb-desc">{{ $screen['description'] }}</p>
        @endif
            </div>
            @if ($guestHouseIndex)
                <div class="tb-lead-actions">
                    <a class="tb-btn tb-btn-md" href="{{ \App\Filament\Pages\ListingReviewPage::getUrl() }}">
                        <img src="{{ asset('admin-redesign/icon-tb-approval.svg') }}" alt="" width="16" height="16">
                        Approval queue
                    </a>
                    @if (\App\Filament\GuestHouse\Resources\GuestHouseResource::canCreate())
                        <a class="tb-btn tb-btn-md tb-btn-primary" href="{{ \App\Filament\GuestHouse\Resources\GuestHouseResource::getUrl('create') }}">
                            <img src="{{ asset('admin-redesign/icon-tb-plus-white.svg') }}" alt="" width="16" height="16">
                            New guest house
                        </a>
                    @endif
                </div>
            @elseif ($vehicleIndex)
                <div class="tb-lead-actions">
                    <a class="tb-btn tb-btn-md" href="{{ \App\Filament\Pages\ListingReviewPage::getUrl() }}">
                        <img src="{{ asset('admin-redesign/icon-tb-approval.svg') }}" alt="" width="16" height="16">
                        Approval queue
                    </a>
                    <a class="tb-btn tb-btn-md" href="{{ \App\Filament\Resources\DailyFares\DailyFareResource::getUrl('index') }}">
                        <img src="{{ asset('admin-redesign/icon-tb-pricing.svg') }}" alt="" width="16" height="16">
                        Pricing
                    </a>
                    @if (\App\Filament\Resources\Cars\CarResource::canCreate())
                        <a class="tb-btn tb-btn-md tb-btn-primary" href="{{ \App\Filament\Resources\Cars\CarResource::getUrl('create') }}">
                            <img src="{{ asset('admin-redesign/icon-tb-plus-white.svg') }}" alt="" width="16" height="16">
                            Add vehicle
                        </a>
                    @endif
                </div>
            @elseif ($headerActions !== [])
                <div class="tb-lead-actions">
                    @foreach ($headerActions as $action)
                        @if ($action['event'] ?? null)
                            <button type="button" @class(['tb-btn tb-btn-md', 'tb-btn-primary' => $action['primary']]) onclick="Livewire.find(document.getElementById('tb-amenity-board').dataset.wireId).mountAction('create')">
                                <img src="{{ asset($action['primary'] ? 'admin-redesign/icon-tb-plus-white.svg' : 'admin-redesign/icon-tb-plus.svg') }}" alt="" width="16" height="16">
                                {{ $action['label'] }}
                            </button>
                        @elseif ($action['mount'] ?? null)
                            <button type="button" @class(['tb-btn tb-btn-md', 'tb-btn-primary' => $action['primary']]) onclick="var root=this.closest('[wire\\:id]'); if(root) Livewire.find(root.getAttribute('wire:id')).mountAction(@js($action['mount']))">
                                {{ $action['label'] }}
                            </button>
                        @elseif ($action['wire'])
                            <button type="button" @class(['tb-btn tb-btn-md', 'tb-btn-primary' => $action['primary']]) wire:click="{{ $action['wire'] }}">
                                <img src="{{ asset($action['primary'] ? 'admin-redesign/icon-tb-plus-white.svg' : 'admin-redesign/icon-tb-plus.svg') }}" alt="" width="16" height="16">
                                {{ $action['label'] }}
                            </button>
                        @else
                            <a @class(['tb-btn tb-btn-md', 'tb-btn-primary' => $action['primary']]) href="{{ $action['url'] }}">
                                <img src="{{ asset($action['icon'] ?? ($action['primary'] ? 'admin-redesign/icon-tb-plus-white.svg' : 'admin-redesign/icon-tb-plus.svg')) }}" alt="" width="16" height="16">
                                {{ $action['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
        @if ($links !== [])
            <nav class="tb-subnav" aria-label="Section">
                @foreach ($links as $link)
                    @if ($link['group'] && $link['group'] !== $group)
                        @php $group = $link['group']; @endphp
                        <span class="tb-subnav-group">{{ $group }}</span>
                    @endif
                    <a href="{{ $link['url'] }}" @class(['is-active' => $link['active']])>
                        @if (! empty($link['icon']))
                            @if (str_ends_with((string) $link['icon'], '.svg'))
                                <img src="{{ asset('admin-redesign/'.$link['icon']) }}" alt="" width="16" height="16">
                            @else
                                <x-admin.icon :name="$link['icon']" :size="16" />
                            @endif
                        @endif
                        {{ $link['label'] }}
                        @if (array_key_exists('count', $link) && $link['count'] !== null)
                            <span class="tb-count">{{ $link['count'] }}</span>
                        @endif
                        @if (! empty($link['dot']))
                            <span class="tb-nav-dot" title="Needs a check"></span>
                        @endif
                    </a>
                @endforeach
            </nav>
        @endif
    </div>
@endif
