@php
    $path = request()->path();

    if (request()->hasHeader('X-Livewire') && ($__ghReferer = request()->header('referer'))) {
        $path = ltrim(parse_url($__ghReferer, PHP_URL_PATH) ?? $path, '/');
    }

    $inGuestHouses = str_starts_with($path, 'admin/guest-houses');
    $showGhTabs = $inGuestHouses;

    $icons = [
        'properties' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
        'bookings'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6.75 3v2.25M17.25 3v2.25M3 9.75h18M4.5 6.75h15a1.5 1.5 0 0 1 1.5 1.5v12a1.5 1.5 0 0 1-1.5 1.5h-15a1.5 1.5 0 0 1-1.5-1.5v-12a1.5 1.5 0 0 1 1.5-1.5Z"/>',
        'content'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.563.563 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>',
        'moderation' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>',
        'setup'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
    ];

    $quickLinks = [
        [
            'label'  => 'Properties',
            'icon'   => $icons['properties'],
            'url'    => url('/admin/guest-houses/guest-houses'),
            'active' => request()->is('admin/guest-houses/guest-houses*'),
        ],
        [
            'label'  => 'Bookings',
            'icon'   => $icons['bookings'],
            'active' => request()->is('admin/guest-houses/guest-house-bookings*')
                || request()->is('admin/guest-houses/calendar*'),
            'items'  => [
                ['label' => 'Bookings', 'url' => url('/admin/guest-houses/guest-house-bookings'), 'active' => request()->is('admin/guest-houses/guest-house-bookings*')],
                ['label' => 'Calendar',   'url' => url('/admin/guest-houses/calendar'),           'active' => request()->is('admin/guest-houses/calendar*')],
            ],
        ],
        [
            'label'  => 'Content',
            'icon'   => $icons['content'],
            'url'    => url('/admin/listing-reviews'),
            'active' => request()->is('admin/listing-reviews*')
                || request()->is('admin/guest-houses/guest-reviews*'),
        ],
        [
            'label'  => 'Listing approvals',
            'icon'   => $icons['moderation'],
            'url'    => url('/admin/listing-approvals'),
            'active' => request()->is('admin/listing-approvals*'),
        ],
        [
            'label'  => 'Setup',
            'icon'   => $icons['setup'],
            'url'    => url('/admin/guest-houses/guest-house-amenities'),
            'active' => request()->is('admin/guest-houses/guest-house-amenities*'),
        ],
    ];

    $mobileNavOptions = [];
    foreach ($quickLinks as $link) {
        if (! empty($link['items'])) {
            foreach ($link['items'] as $sub) {
                if (empty($sub['url'])) {
                    continue;
                }
                $mobileNavOptions[] = [
                    'label' => $link['label'].' — '.$sub['label'],
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

@if ($inGuestHouses)
    <style>
        .fi-page-sub-navigation-tabs,
        .fi-page-sub-navigation-dropdown {
            display: none !important;
        }
    </style>
@endif

@if ($showGhTabs)
    @include('filament.panel.partials.cluster-subnav-bar', [
        'quickLinks' => $quickLinks,
        'mobileNavOptions' => $mobileNavOptions,
        'activeMobileUrl' => $activeMobileUrl,
        'mobileLabel' => 'Guest Houses section',
        'ariaLabel' => 'Guest Houses navigation',
        'dataPrefix' => 'gh',
    ])
@endif

<script>
(function () {
    if (window.__ghTabsNavCleanup) {
        return;
    }

    window.__ghTabsNavCleanup = true;

    function shouldShowGhTabs(pathname) {
        return pathname.includes('/admin/guest-houses');
    }

    document.addEventListener('livewire:navigated', function () {
        if (shouldShowGhTabs(window.location.pathname)) {
            return;
        }

        document.querySelectorAll('[data-gh-quick-access]').forEach(function (el) {
            el.remove();
        });
    });
})();
</script>
