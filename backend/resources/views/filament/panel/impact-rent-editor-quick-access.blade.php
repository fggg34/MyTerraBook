@php
    $path = request()->path();

    // During Livewire AJAX updates (e.g. clicking a table tab via $set), this
    // render hook re-runs but request()->path() is "livewire/update" rather than
    // the real page path. Derive the actual page path from the Referer so the
    // gate stays stable and Livewire's DOM morph doesn't strip the nav.
    if (request()->hasHeader('X-Livewire') && ($__irReferer = request()->header('referer'))) {
        $path = ltrim(parse_url($__irReferer, PHP_URL_PATH) ?? $path, '/');
    }

    $inImpactRent = str_starts_with($path, 'admin/impact-rent');

    $showIrTabs = $inImpactRent;

    /*
     * Each top-level item can be:
     *   - a direct link  →  ['label', 'icon', 'url', 'active']
     *   - a dropdown     →  ['label', 'icon', 'active', 'items' => [['label','url','active'], ...]]
     *
     * Icons are inline SVG paths (heroicons-style 24x24 outline).
     */
    $icon = function (string ...$paths): string {
        return collect($paths)
            ->map(fn (string $d): string => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="'.$d.'"/>')
            ->implode('');
    };

    $icons = [
        'vehicles' => $icon('M8.25 18.75a1.5 1.5 0 0 1-3 0m15 0a1.5 1.5 0 0 1-3 0m-9.75 0H4.875A1.125 1.125 0 0 1 3.75 17.625V14.25m15.75-1.5v4.875c0 .621-.504 1.125-1.125 1.125h-1.875m1.5-6.75H4.5m15 0-1.1-5.7a1.125 1.125 0 0 0-1.106-.925H6.706a1.125 1.125 0 0 0-1.106.945L4.5 12.75'),
        'units' => $icon('M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'),
        'folder' => $icon('M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z'),
        'layers' => $icon('M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122'),
        'sparkles' => $icon('M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z', 'M18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z'),
        'pin' => $icon('M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z', 'M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'),
        'document' => $icon('M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'),
        'tag' => $icon('M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z', 'M6 6h.008v.008H6V6Z'),
        'banknotes' => $icon('M2.25 18.75a60.07 60.07 0 0 0 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'),
        'clock' => $icon('M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'),
        'bolt' => $icon('M3.75 13.5 10.5 2.25 12 10.5h8.25L9.75 21.75 12 13.5H3.75Z'),
        'moon' => $icon('M21.752 15.002A9.718 9.718 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z'),
        'map' => $icon('M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z'),
        'cube' => $icon('M21 7.5 12 2.25 3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'),
        'ticket' => $icon('M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 0 1 0 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 0 1 0-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375Z'),
        'receipt' => $icon('M9 14.25 15 8.25m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z', 'M9.75 9h.008v.008H9.75V9Z', 'M14.25 13.5h.008v.008h-.008V13.5Z'),
        'list' => $icon('M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z'),
        'chart' => $icon('M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'),
        'calendar' => $icon('M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v11.25a1.5 1.5 0 0 1-1.5 1.5h-15a1.5 1.5 0 0 1-1.5-1.5V8.25a1.5 1.5 0 0 1 1.5-1.5Z'),
        'clipboard' => $icon('M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z'),
        'ban' => $icon('M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636'),
        'users' => $icon('M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 1 1 5.25 0Z'),
        'pie' => $icon('M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z', 'M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z'),
        'megaphone' => $icon('M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46'),
    ];

    $quickLinks = [
        [
            'label'  => 'Vehicles',
            'icon'   => $icons['vehicles'],
            'active' => request()->is('admin/impact-rent/cars*')
                || request()->is('admin/impact-rent/car-units*')
                || request()->is('admin/impact-rent/main-categories*')
                || request()->is('admin/impact-rent/sub-categories*')
                || request()->is('admin/impact-rent/characteristics*')
                || request()->is('admin/impact-rent/locations*')
                || request()->is('admin/impact-rent/rental-conditions*'),
            'items'  => [
                ['label' => 'All vehicles',      'icon' => $icons['vehicles'],  'url' => url('/admin/impact-rent/cars'),               'active' => request()->is('admin/impact-rent/cars', 'admin/impact-rent/cars/')],
                ['label' => 'Fleet units',       'icon' => $icons['units'],     'url' => url('/admin/impact-rent/car-units'),          'active' => request()->is('admin/impact-rent/car-units*')],
                ['label' => 'Main Categories',   'icon' => $icons['folder'],    'url' => url('/admin/impact-rent/main-categories'),    'active' => request()->is('admin/impact-rent/main-categories*')],
                ['label' => 'Sub Categories',    'icon' => $icons['layers'],    'url' => url('/admin/impact-rent/sub-categories'),     'active' => request()->is('admin/impact-rent/sub-categories*')],
                ['label' => 'Features',          'icon' => $icons['sparkles'],  'url' => url('/admin/impact-rent/characteristics'),    'active' => request()->is('admin/impact-rent/characteristics*')],
                ['label' => 'Locations',         'icon' => $icons['pin'],       'url' => url('/admin/impact-rent/locations'),          'active' => request()->is('admin/impact-rent/locations*')],
                ['label' => 'Rental Conditions', 'icon' => $icons['document'],  'url' => url('/admin/impact-rent/rental-conditions'),  'active' => request()->is('admin/impact-rent/rental-conditions*')],
            ],
        ],
        [
            'label'  => 'Pricing',
            'icon'   => $icons['tag'],
            'active' => request()->is('admin/impact-rent/special-prices*')
                || request()->is('admin/impact-rent/daily-fares*')
                || request()->is('admin/impact-rent/extra-hour-fares*')
                || request()->is('admin/impact-rent/hourly-fares*')
                || request()->is('admin/impact-rent/out-of-hours-fees*')
                || request()->is('admin/impact-rent/location-fees*')
                || request()->is('admin/impact-rent/rental-options*')
                || request()->is('admin/impact-rent/coupons*')
                || request()->is('admin/impact-rent/tax-rates*')
                || request()->is('admin/impact-rent/price-types*')
                || request()->is('admin/impact-rent/fares-overview*'),
            'items'  => [
                ['label' => 'Pricing rules',     'icon' => $icons['tag'],       'url' => url('/admin/impact-rent/special-prices'),     'active' => request()->is('admin/impact-rent/special-prices*')],
                ['label' => 'Base prices',       'icon' => $icons['banknotes'], 'url' => url('/admin/impact-rent/daily-fares'),        'active' => request()->is('admin/impact-rent/daily-fares*')],
                ['label' => 'Extra hour fares',  'icon' => $icons['clock'],     'url' => url('/admin/impact-rent/extra-hour-fares'),   'active' => request()->is('admin/impact-rent/extra-hour-fares*')],
                ['label' => 'Hourly fares',      'icon' => $icons['bolt'],      'url' => url('/admin/impact-rent/hourly-fares'),       'active' => request()->is('admin/impact-rent/hourly-fares*')],
                ['label' => 'Out-of-hours fees', 'icon' => $icons['moon'],      'url' => url('/admin/impact-rent/out-of-hours-fees'),  'active' => request()->is('admin/impact-rent/out-of-hours-fees*')],
                ['label' => 'Location fees',     'icon' => $icons['map'],       'url' => url('/admin/impact-rent/location-fees'),      'active' => request()->is('admin/impact-rent/location-fees*')],
                ['label' => 'Add-ons',           'icon' => $icons['cube'],      'url' => url('/admin/impact-rent/rental-options'),     'active' => request()->is('admin/impact-rent/rental-options*')],
                ['label' => 'Discount codes',    'icon' => $icons['ticket'],    'url' => url('/admin/impact-rent/coupons'),            'active' => request()->is('admin/impact-rent/coupons*')],
                ['label' => 'Tax Rates',         'icon' => $icons['receipt'],   'url' => url('/admin/impact-rent/tax-rates'),          'active' => request()->is('admin/impact-rent/tax-rates*')],
                ['label' => 'Types of Price',    'icon' => $icons['list'],      'url' => url('/admin/impact-rent/price-types'),        'active' => request()->is('admin/impact-rent/price-types*')],
                ['label' => 'Fares Overview',    'icon' => $icons['chart'],     'url' => url('/admin/impact-rent/fares-overview'),     'active' => request()->is('admin/impact-rent/fares-overview*')],
            ],
        ],
        [
            'label'  => 'Bookings',
            'icon'   => $icons['calendar'],
            'active' => request()->is('admin/impact-rent/orders-calendar*')
                || request()->is('admin/impact-rent/orders')
                || request()->is('admin/impact-rent/orders/*')
                || request()->is('admin/impact-rent/booking-restrictions*')
                || request()->is('admin/registered-clients*'),
            'items'  => [
                ['label' => 'Calendar',      'icon' => $icons['calendar'],  'url' => url('/admin/impact-rent/orders-calendar'),     'active' => request()->is('admin/impact-rent/orders-calendar*')],
                ['label' => 'Reservations',  'icon' => $icons['clipboard'], 'url' => url('/admin/impact-rent/orders'),              'active' => request()->is('admin/impact-rent/orders') || request()->is('admin/impact-rent/orders/*')],
                ['label' => 'Restrictions',  'icon' => $icons['ban'],       'url' => url('/admin/impact-rent/booking-restrictions'), 'active' => request()->is('admin/impact-rent/booking-restrictions*')],
                ['label' => 'Customers',     'icon' => $icons['users'],     'url' => url('/admin/registered-clients'),              'active' => request()->is('admin/registered-clients*')],
            ],
        ],
        [
            'label'  => 'Finance',
            'icon'   => $icons['chart'],
            'active' => request()->is('admin/impact-rent/reports*')
                || request()->is('admin/impact-rent/tracking-statistics*')
                || request()->is('admin/impact-rent/tracking-campaigns*'),
            'items'  => [
                ['label' => 'Reports',              'icon' => $icons['pie'],       'url' => url('/admin/impact-rent/reports'),              'active' => request()->is('admin/impact-rent/reports*')],
                ['label' => 'Statistics Tracking',  'icon' => $icons['chart'],     'url' => url('/admin/impact-rent/tracking-statistics'),  'active' => request()->is('admin/impact-rent/tracking-statistics*')],
                ['label' => 'Tracking campaigns',   'icon' => $icons['megaphone'], 'url' => url('/admin/impact-rent/tracking-campaigns'),   'active' => request()->is('admin/impact-rent/tracking-campaigns*')],
            ],
        ],
    ];

    // Hide Phase 2 / unimplemented items (null URL) from dropdowns.
    $quickLinks = array_values(array_map(function (array $link): array {
        if (! empty($link['items'])) {
            $link['items'] = array_values(array_filter(
                $link['items'],
                fn (array $item): bool => ! empty($item['url']),
            ));
        }

        return $link;
    }, $quickLinks));

    $mobileNavOptions = [];
    foreach ($quickLinks as $link) {
        if (! empty($link['items'])) {
            foreach ($link['items'] as $sub) {
                if (empty($sub['url'])) {
                    continue;
                }
                $mobileNavOptions[] = [
                    'label' => $link['label'] === $sub['label'] ? $link['label'] : $link['label'].' / '.$sub['label'],
                    'url' => $sub['url'],
                    'active' => (bool) ($sub['active'] ?? false),
                ];
            }
        } elseif (! empty($link['url'])) {
            $mobileNavOptions[] = [
                'label' => $link['label'],
                'url' => $link['url'],
                'active' => (bool) ($link['active'] ?? false),
            ];
        }
    }

    $activeMobileUrl = collect($mobileNavOptions)->firstWhere('active', true)['url'] ?? '';
@endphp

@if ($inImpactRent)
    <script>
        document.documentElement.classList.add('ir-shell');
    </script>
    <style>
        .fi-page-sub-navigation-tabs,
        .fi-page-sub-navigation-dropdown {
            display: none !important;
        }

        html.ir-shell,
        html.ir-shell body,
        html.ir-shell .fi-body,
        html.ir-shell .fi-layout,
        html.ir-shell .fi-main-ctn,
        html.ir-shell .fi-main,
        html.ir-shell .fi-page {
            background: #f4f6f9;
            color: #0f172a;
        }

        html.ir-shell .fi-breadcrumbs {
            display: none;
        }

        html.ir-shell .fi-header {
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 0.75rem 1.5rem;
        }

        html.ir-shell .fi-header > div:first-child {
            flex: 1 1 22rem;
            min-width: min(100%, 22rem);
        }

        @media (max-width: 1100px) {
            html.ir-shell .fi-header {
                flex-direction: column;
            }

            html.ir-shell .fi-header-actions-ctn {
                margin-left: 0;
            }
        }

        html.ir-shell .fi-header-actions-ctn {
            margin-left: auto;
        }

        html.ir-shell .fi-topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: none;
        }

        html.ir-shell .fi-page {
            gap: 1.5rem;
        }

        html.ir-shell .fi-header-heading {
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -0.025em;
            color: #0f172a;
        }

        html.ir-shell .fi-header-subheading {
            max-width: 40rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        html.ir-shell .fi-header-actions-ctn .fi-btn {
            border-radius: 0.5rem;
            box-shadow: none;
        }

        html.ir-shell .fi-header-actions-ctn .fi-btn.fi-color-gray {
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #334155;
        }

        html.ir-shell .fi-header-actions-ctn .fi-btn.fi-color-success,
        html.ir-shell .fi-header-actions-ctn .fi-btn.fi-color-primary {
            height: 2.75rem;
            padding-inline: 1.25rem;
            background: #059669;
            border-color: transparent;
            color: #fff;
            font-weight: 600;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.06);
        }

        html.ir-shell .fi-header-actions-ctn .fi-btn.fi-color-success:hover,
        html.ir-shell .fi-header-actions-ctn .fi-btn.fi-color-primary:hover {
            background: #047857;
        }

        html.ir-shell .fi-btn.fi-color-primary {
            background-color: #0164a3;
            border-color: transparent;
        }

        html.ir-shell .fi-btn.fi-color-primary:hover {
            background-color: #014f82;
        }

        html.ir-shell .fi-tabs:not(.fi-contained) {
            width: fit-content;
            margin-inline: 0;
            border-radius: 0.5rem;
            background: #f1f5f9;
            padding: 3px;
            box-shadow: none;
            outline: none;
        }

        html.ir-shell .fi-tabs-item {
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }

        html.ir-shell .fi-tabs-item.fi-active {
            background: #fff;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.06);
        }

        html.ir-shell .fi-tabs-item .fi-tabs-item-label {
            color: #64748b;
            font-weight: 500;
        }

        html.ir-shell .fi-tabs-item.fi-active .fi-tabs-item-label {
            color: #0f172a;
            font-weight: 600;
        }

        html.ir-shell .fi-main .fi-ta-ctn,
        html.ir-shell .fi-main .fi-section,
        html.ir-shell .fi-main .fi-sc-section,
        html.ir-shell .fi-main .fi-wi-stats-overview-stat,
        html.ir-shell .fi-modal-content,
        html.ir-shell .fi-dropdown-panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
        }

        html.ir-shell .fi-ta-ctn {
            overflow: hidden;
        }

        html.ir-shell body.ir-orders-page .fi-ta-ctn,
        html.ir-shell body.ir-orders-page .fi-ta-content-ctn {
            overflow: visible;
        }

        html.ir-shell .fi-ta-header,
        html.ir-shell .fi-ta-header-ctn,
        html.ir-shell .fi-ta-header-toolbar,
        html.ir-shell .fi-ta-filters {
            background: #fff;
        }

        html.ir-shell .fi-ta-header-toolbar {
            border-bottom: 1px solid #f1f5f9;
            padding: 0.75rem 1rem;
        }

        html.ir-shell .fi-ta-table {
            border-collapse: separate;
        }

        html.ir-shell .fi-ta-header-cell,
        html.ir-shell .fi-ta-header-cell-sort-btn {
            background: #fff;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        html.ir-shell .fi-ta-row {
            border-bottom: 1px solid #f1f5f9;
        }

        html.ir-shell .fi-ta-row.fi-striped,
        html.ir-shell .fi-ta-row:nth-child(even) {
            background: #fff;
        }

        html.ir-shell .fi-ta-row:hover {
            background: #f8fafc;
        }

        html.ir-shell .fi-ta-cell {
            padding-top: 0.875rem;
            padding-bottom: 0.875rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        html.ir-shell .fi-ta-text-item-label {
            color: #0f172a;
        }

        html.ir-shell .fi-ta-text-description {
            color: #64748b;
            font-size: 0.75rem;
        }

        html.ir-shell .fi-badge {
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        html.ir-shell .fi-badge.fi-color-success {
            background: #ecfdf5;
            color: #065f46;
        }

        html.ir-shell .fi-badge.fi-color-gray,
        html.ir-shell .fi-badge.fi-color-neutral {
            background: #f1f5f9;
            color: #475569;
        }

        html.ir-shell .fi-badge.fi-color-warning {
            background: #fffbeb;
            color: #92400e;
        }

        html.ir-shell .fi-badge.fi-color-danger {
            background: #fef2f2;
            color: #991b1b;
        }

        html.ir-shell .fi-badge.fi-color-info,
        html.ir-shell .fi-badge.fi-color-primary {
            background: #e8f1f8;
            color: #014f82;
        }

        html.ir-shell .fi-input-wrp {
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background: #fff;
            box-shadow: none;
        }

        html.ir-shell .fi-input-wrp:focus-within {
            border-color: #0164a3;
            box-shadow: 0 0 0 3px color-mix(in srgb, #0164a3 18%, transparent);
        }

        html.ir-shell .fi-input-wrp.fi-disabled {
            background: #f8fafc;
        }

        html.ir-shell .fi-input-wrp.fi-invalid {
            border-color: #ef4444;
        }

        html.ir-shell .fi-input-wrp.fi-invalid:focus-within {
            box-shadow: 0 0 0 3px color-mix(in srgb, #ef4444 18%, transparent);
        }

        html.ir-shell input.fi-input,
        html.ir-shell select.fi-select-input,
        html.ir-shell textarea.fi-input,
        html.ir-shell .fi-select-input-btn,
        html.ir-shell .fi-fo-date-time-picker-display-text-input {
            background: transparent;
            color: #0f172a;
        }

        html.ir-shell input.fi-input::placeholder,
        html.ir-shell textarea.fi-input::placeholder,
        html.ir-shell .fi-select-input-placeholder {
            color: #94a3b8;
        }

        html.ir-shell .fi-toggle.fi-toggle-on {
            background: #0f172a;
        }

        html.ir-shell .fi-toggle.fi-toggle-off {
            background: #e2e8f0;
        }

        html.ir-shell input[type='checkbox'].fi-checkbox-input:checked,
        html.ir-shell input[type='checkbox'].fi-checkbox-input:indeterminate {
            background-color: #0f172a;
        }

        html.ir-shell .fi-main .fi-section-header,
        html.ir-shell .fi-main .fi-sc-section-header {
            background: #f8fafc;
        }

        html.ir-shell .fi-main .fi-section-content-ctn,
        html.ir-shell .fi-main .fi-section-content {
            background: #fff;
        }

        html.ir-shell .fi-fo-field-label,
        html.ir-shell .fi-fo-field-label-content,
        html.ir-shell .fi-in-entry-label {
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.25rem;
            color: #0f172a;
        }

        html.ir-shell .fi-fo-field {
            gap: 0.375rem;
        }

        html.ir-shell .fi-fo-field .fi-sc-text,
        html.ir-shell .fi-in-entry .fi-sc-text {
            font-size: 0.75rem;
            line-height: 1rem;
            color: #64748b;
        }

        html.ir-shell .fi-section-header-heading {
            font-size: 0.95rem;
            font-weight: 600;
            color: #0f172a;
        }

        html.ir-shell .fi-ta-empty-state {
            background: #fff;
            padding: 4rem 1.5rem;
        }

        html.ir-shell .fi-ta-empty-state-heading {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
        }

        html.ir-shell .fi-ta-empty-state-description {
            color: #64748b;
            font-size: 0.875rem;
        }

        html.ir-shell .fi-page-content {
            max-width: 1400px;
        }

        html.ir-shell .fi-section,
        html.ir-shell .fi-sc-section {
            overflow: visible;
            border-radius: 0.75rem;
        }

        html.ir-shell .fi-section-header,
        html.ir-shell .fi-sc-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-top-left-radius: 0.75rem;
            border-top-right-radius: 0.75rem;
            padding: 1rem 1.25rem;
        }

        html.ir-shell .fi-section-header-description,
        html.ir-shell .fi-sc-section-header-description {
            font-size: 0.875rem;
            color: #64748b;
        }

        html.ir-shell .fi-section-content,
        html.ir-shell .fi-sc-section-content {
            padding: 1.25rem;
        }

        html.ir-shell .fi-section-content.fi-grid {
            align-items: start;
            gap: 1rem;
        }

        html.ir-shell .fi-input-wrp {
            align-items: stretch;
            min-height: 2.25rem;
        }

        html.ir-shell .fi-input-wrp:has(textarea) {
            min-height: 0;
        }

        html.ir-shell input.fi-input,
        html.ir-shell select.fi-select-input,
        html.ir-shell .fi-fo-date-time-picker-display-text-input {
            height: 2.25rem;
            min-height: 2.25rem;
            line-height: 2.25rem;
            padding-top: 0;
            padding-bottom: 0;
        }

        html.ir-shell textarea.fi-input,
        html.ir-shell .fi-textarea {
            height: auto;
            min-height: 4.5rem;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }

        html.ir-shell div.fi-select-input {
            display: flex;
            flex: 1 1 auto;
            min-height: 0;
        }

        html.ir-shell .fi-select-input .fi-select-input-btn,
        html.ir-shell .fi-select-input div[x-ref="select"] {
            min-height: 2.25rem;
        }

        html.ir-shell .fi-fo-date-time-picker-trigger {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 2.25rem;
        }

        html.ir-shell .ir-price-field .fi-in-entry-content {
            display: flex;
            align-items: center;
            min-height: 2.25rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background: #f8fafc;
            padding: 0.375rem 0.75rem;
            color: #334155;
            font-size: 0.875rem;
        }

        html.ir-shell .ir-available-field,
        html.ir-shell .ir-units-field {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #f8fafc;
            padding: 0.75rem 1rem;
        }

        html.ir-shell .ir-available-field .fi-fo-field-label-col,
        html.ir-shell .ir-units-field .fi-fo-field-label-col {
            height: auto;
        }

        html.ir-shell .ir-available-field .fi-fo-field-content-col,
        html.ir-shell .ir-units-field .fi-fo-field-content-col {
            width: auto;
        }

        html.ir-shell .ir-units-field .fi-input-wrp {
            width: 7.5rem;
            background: #fff;
        }

        html.ir-shell .fi-fo-file-upload,
        html.ir-shell .filepond--root {
            border: 2px dashed #e2e8f0;
            border-radius: 0.75rem;
            background: #f8fafc;
        }

        html.ir-shell .fi-fo-checkbox-list-options {
            gap: 0.5rem;
        }

        html.ir-shell .fi-fo-checkbox-list-option {
            margin: 0;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #fff;
            padding: 0.6rem 0.75rem;
        }

        html.ir-shell .fi-fo-checkbox-list-option:has(:checked) {
            border-color: color-mix(in srgb, #0f172a 40%, white);
            background: #f1f5f9;
            font-weight: 600;
        }

        html.ir-shell .fi-sc-wizard {
            gap: 1.25rem;
        }

        html.ir-shell .fi-sc-wizard-header {
            display: flex;
            overflow-x: auto;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #fff;
            box-shadow: none;
            padding: 0.375rem;
            gap: 0.125rem;
        }

        html.ir-shell .fi-sc-wizard-header-step {
            display: flex;
            position: relative;
            flex: 0 0 auto;
        }

        html.ir-shell .fi-sc-wizard-header-step-separator {
            display: none !important;
        }

        html.ir-shell .fi-sc-wizard-header-step-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            height: auto;
            padding: 0.5rem 0.75rem;
            border-radius: 0.75rem;
            color: #64748b;
        }

        html.ir-shell .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-btn {
            background: #0f172a;
            color: #fff;
        }

        html.ir-shell .fi-sc-wizard-header-step-icon-ctn {
            width: 1.25rem;
            height: 1.25rem;
            border-width: 0 !important;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
        }

        html.ir-shell .fi-sc-wizard-header-step.fi-completed .fi-sc-wizard-header-step-icon-ctn {
            background: #0f172a;
            color: #fff;
        }

        html.ir-shell .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-icon-ctn {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        html.ir-shell .fi-sc-wizard-header-step-number {
            font-size: 11px;
            font-weight: 700;
            color: inherit;
        }

        html.ir-shell .fi-sc-wizard-header-step-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: inherit;
        }

        html.ir-shell .fi-sc-wizard-header-step.fi-active .fi-sc-wizard-header-step-label {
            color: #fff;
        }

        html.ir-shell .fi-sc-wizard-header-step-description {
            display: none;
        }

        html.ir-shell .fi-sc-wizard-footer {
            position: sticky;
            bottom: 1rem;
            z-index: 10;
            margin-top: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: color-mix(in srgb, #fff 95%, transparent);
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
            padding: 0.75rem 1rem;
            backdrop-filter: blur(8px);
        }

        html.ir-shell .fi-sc-wizard-footer .fi-btn.fi-color-success {
            background: #059669;
            border-color: transparent;
            color: #fff;
            font-weight: 600;
        }

        html.ir-shell .fi-form-actions {
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            padding: 0.75rem 1rem;
        }

        html.ir-shell .fi-fo-placeholder {
            color: #64748b;
            font-size: 0.875rem;
        }

        .ir-rail {
            display: none;
        }

        .ir-rail-mobile {
            display: none;
        }

        @media (min-width: 720px) {
            html.ir-shell .fi-topbar-ctn,
            html.ir-shell .fi-layout {
                padding-left: 15rem;
            }

            html.ir-shell .fi-sidebar.fi-main-sidebar,
            html.ir-shell .fi-sidebar-close-overlay,
            html.ir-shell .fi-topbar-open-sidebar-btn,
            html.ir-shell .fi-topbar-close-sidebar-btn,
            html.ir-shell .fi-topbar-collapse-sidebar-btn-ctn,
            html.ir-shell .fi-topbar-open-collapse-sidebar-btn,
            html.ir-shell .fi-topbar-close-collapse-sidebar-btn {
                display: none !important;
            }

            .ir-rail {
                display: flex;
                position: fixed;
                z-index: 40;
                top: 0;
                left: 0;
                width: 15rem;
                height: 100vh;
                flex-direction: column;
                background: #fff;
                border-right: 1px solid #e2e8f0;
                color: #0f172a;
            }
        }

        @media (max-width: 719px) {
            .ir-rail-mobile {
                display: block;
                margin: 0 0 1rem;
            }
        }

        .ir-rail__brand {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            height: 3.5rem;
            padding: 0 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .ir-rail__mark {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.375rem;
            background: #0164a3;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .ir-rail__name {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.2;
        }

        .ir-rail__sub {
            margin: 0;
            font-size: 0.75rem;
            color: #64748b;
        }

        .ir-rail__nav {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .ir-rail__group + .ir-rail__group {
            margin-top: 0.75rem;
        }

        .ir-rail__label {
            margin: 0;
            padding: 0.375rem 0.625rem;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }

        .ir-rail__group--active .ir-rail__label {
            color: #0164a3;
        }

        .ir-rail__link {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-top: 0.125rem;
            padding: 0.5rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            color: #64748b;
            text-decoration: none;
        }

        .ir-rail__link:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .ir-rail__link--active {
            background: color-mix(in srgb, #0164a3 12%, white);
            color: #0164a3;
            font-weight: 600;
        }

        .ir-rail__icon {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
        }

        .ir-rail__foot {
            border-top: 1px solid #e2e8f0;
            padding: 0.75rem;
        }

        .ir-rail__back {
            display: block;
            padding: 0.4rem 0.25rem;
            font-size: 0.75rem;
            color: #64748b;
            text-decoration: none;
        }

        .ir-rail__back:hover {
            color: #0f172a;
        }

        .ir-rail-mobile__label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
        }

        .ir-rail-mobile__select {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background: #fff;
            padding: 0.55rem 0.75rem;
            font-size: 0.875rem;
        }
    </style>
@endif

@if ($showIrTabs)
    <aside class="ir-rail" data-ir-quick-access>
        <div class="ir-rail__brand">
            <div class="ir-rail__mark" aria-hidden="true">IR</div>
            <div>
                <p class="ir-rail__name">Impact Rent</p>
                <p class="ir-rail__sub">Rental admin</p>
            </div>
        </div>
        <nav class="ir-rail__nav" aria-label="Impact Rent navigation">
            @foreach ($quickLinks as $link)
                <div class="ir-rail__group {{ ! empty($link['active']) ? 'ir-rail__group--active' : '' }}">
                    <p class="ir-rail__label">{{ $link['label'] }}</p>
                    @foreach ($link['items'] as $sub)
                        <a
                            href="{{ $sub['url'] }}"
                            class="ir-rail__link {{ ($sub['active'] ?? false) ? 'ir-rail__link--active' : '' }}"
                            @if ($sub['active'] ?? false) aria-current="page" @endif
                        >
                            <svg class="ir-rail__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                {!! $sub['icon'] ?? $link['icon'] !!}
                            </svg>
                            <span>{{ $sub['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
        <div class="ir-rail__foot">
            <a class="ir-rail__back" href="{{ url('/admin') }}">Terrabook admin</a>
        </div>
    </aside>

    <div class="ir-rail-mobile" data-ir-quick-access>
        <label class="ir-rail-mobile__label" for="ir-rail-mobile-select">Impact Rent section</label>
        <select
            id="ir-rail-mobile-select"
            class="ir-rail-mobile__select"
            aria-label="Impact Rent section"
            onchange="if (this.value) window.location.href = this.value"
        >
            @foreach ($mobileNavOptions as $option)
                <option value="{{ $option['url'] }}" @selected($option['url'] === $activeMobileUrl)>
                    {{ $option['label'] }}
                </option>
            @endforeach
        </select>
    </div>
@endif

<script>
(function () {
    if (window.__irTabsNavCleanup) {
        return;
    }

    window.__irTabsNavCleanup = true;

    function shouldShowIrTabs(pathname) {
        return pathname.includes('/admin/impact-rent');
    }

    document.addEventListener('livewire:navigated', function () {
        if (shouldShowIrTabs(window.location.pathname)) {
            document.documentElement.classList.add('ir-shell');
            return;
        }

        document.documentElement.classList.remove('ir-shell');
        document.querySelectorAll('[data-ir-quick-access]').forEach(function (el) {
            el.remove();
        });
    });
})();
</script>
