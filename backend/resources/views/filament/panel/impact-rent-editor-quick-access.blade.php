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
    $icons = [
        'rental'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/>',
        'cars'       => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8.25 18.75a1.5 1.5 0 0 1-3 0M19.5 18.75a1.5 1.5 0 1 1-3 0m-9.75 0H4.875a1.125 1.125 0 0 1-1.125-1.125V14.25M19.5 12.75v4.875c0 .621-.504 1.125-1.125 1.125h-1.875M19.5 12.75H4.5M19.5 12.75 18.4 7.05a1.125 1.125 0 0 0-1.106-.925H6.706c-.553 0-1.027.4-1.106.945L4.5 12.75"/>',
        'pricing'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 6h.008v.008H6V6Z"/>',
        'orders'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>',
        'management' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>',
        'advanced'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46"/>',
    ];

    $quickLinks = [
        [
            'label'  => 'Rental',
            'icon'   => $icons['rental'],
            'active' => request()->is('admin/impact-rent/tax-rates*')
                || request()->is('admin/impact-rent/price-types*')
                || request()->is('admin/impact-rent/locations*')
                || request()->is('admin/impact-rent/booking-restrictions*'),
            'items'  => [
                ['label' => 'Tax Rates',                 'url' => url('/admin/impact-rent/tax-rates'),            'active' => request()->is('admin/impact-rent/tax-rates*')],
                ['label' => 'Types of Price',            'url' => url('/admin/impact-rent/price-types'),          'active' => request()->is('admin/impact-rent/price-types*')],
                ['label' => 'Pickup/Drop Off Locations', 'url' => url('/admin/impact-rent/locations'),            'active' => request()->is('admin/impact-rent/locations*')],
                ['label' => 'Restrictions',              'url' => url('/admin/impact-rent/booking-restrictions'), 'active' => request()->is('admin/impact-rent/booking-restrictions*')],
            ],
        ],
        [
            'label'  => 'Cars',
            'icon'   => $icons['cars'],
            'active' => request()->is('admin/impact-rent/main-categories*')
                || request()->is('admin/impact-rent/sub-categories*')
                || request()->is('admin/impact-rent/rental-options*')
                || request()->is('admin/impact-rent/rental-conditions*')
                || request()->is('admin/impact-rent/characteristics*')
                || request()->is('admin/impact-rent/cars*'),
            'items'  => [
                ['label' => 'Main Categories', 'url' => url('/admin/impact-rent/main-categories'), 'active' => request()->is('admin/impact-rent/main-categories*')],
                ['label' => 'Sub Categories',  'url' => url('/admin/impact-rent/sub-categories'),  'active' => request()->is('admin/impact-rent/sub-categories*')],
                ['label' => 'Car Options',     'url' => url('/admin/impact-rent/rental-options'),  'active' => request()->is('admin/impact-rent/rental-options*')],
                ['label' => 'Rental Conditions', 'url' => url('/admin/impact-rent/rental-conditions'), 'active' => request()->is('admin/impact-rent/rental-conditions*')],
                ['label' => 'Characteristics', 'url' => url('/admin/impact-rent/characteristics'), 'active' => request()->is('admin/impact-rent/characteristics*')],
                ['label' => 'Cars List',       'url' => url('/admin/impact-rent/cars'),            'active' => request()->is('admin/impact-rent/cars*')],
            ],
        ],
        [
            'label'  => 'Pricing',
            'icon'   => $icons['pricing'],
            'active' => request()->is('admin/impact-rent/daily-fares*')
                || request()->is('admin/impact-rent/extra-hour-fares*')
                || request()->is('admin/impact-rent/special-prices*')
                || request()->is('admin/impact-rent/location-fees*')
                || request()->is('admin/impact-rent/out-of-hours-fees*')
                || request()->is('admin/impact-rent/fares-overview*'),
            'items'  => [
                ['label' => 'Fares Table',          'url' => url('/admin/impact-rent/daily-fares'),       'active' => request()->is('admin/impact-rent/daily-fares*') || request()->is('admin/impact-rent/extra-hour-fares*')],
                ['label' => 'Special Prices',       'url' => url('/admin/impact-rent/special-prices'),    'active' => request()->is('admin/impact-rent/special-prices*')],
                ['label' => 'Pickup/Drop Off Fees', 'url' => url('/admin/impact-rent/location-fees'),     'active' => request()->is('admin/impact-rent/location-fees*')],
                ['label' => 'Out of Hours Fees',    'url' => url('/admin/impact-rent/out-of-hours-fees'), 'active' => request()->is('admin/impact-rent/out-of-hours-fees*')],
                ['label' => 'Fares Overview',       'url' => url('/admin/impact-rent/fares-overview'),    'active' => request()->is('admin/impact-rent/fares-overview*')],
            ],
        ],
        [
            'label'  => 'Orders',
            'icon'   => $icons['orders'],
            'active' => request()->is('admin/impact-rent/orders*')
                || request()->is('admin/impact-rent/orders-calendar*'),
            'items'  => [
                ['label' => 'Orders List', 'url' => url('/admin/impact-rent/orders'), 'active' => request()->is('admin/impact-rent/orders*')],
                ['label' => 'Calendar',    'url' => url('/admin/impact-rent/orders-calendar'), 'active' => request()->is('admin/impact-rent/orders-calendar*')],
                ['label' => 'Overview',    'url' => null,                               'active' => false], // Phase 2
                ['label' => 'Dashboard',   'url' => null,                               'active' => false], // Phase 2
            ],
        ],
        [
            'label'  => 'Management',
            'icon'   => $icons['management'],
            'active' => request()->is('admin/impact-rent/coupons*'),
            'items'  => [
                ['label' => 'Customers',           'url' => null,                               'active' => false], // Phase 2
                ['label' => 'Coupons',             'url' => url('/admin/impact-rent/coupons'), 'active' => request()->is('admin/impact-rent/coupons*')],
                ['label' => 'Graphs & Statistics', 'url' => null,                               'active' => false], // Phase 2
            ],
        ],
        [
            'label'  => 'Advanced',
            'icon'   => $icons['advanced'],
            'active' => request()->is('admin/impact-rent/tracking-campaigns*')
                || request()->is('admin/impact-rent/reports*')
                || request()->is('admin/impact-rent/tracking-statistics*'),
            'items'  => [
                ['label' => 'iCal',                'url' => null,                                           'active' => false], // Phase 2
                ['label' => 'Reports',             'url' => url('/admin/impact-rent/reports'),             'active' => request()->is('admin/impact-rent/reports*')],
                ['label' => 'Statistics Tracking', 'url' => url('/admin/impact-rent/tracking-statistics'), 'active' => request()->is('admin/impact-rent/tracking-statistics*')],
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

@if ($inImpactRent)
    <style>
        /* Hide native Filament cluster sub-nav, replaced by the IR bar below */
        .fi-page-sub-navigation-tabs,
        .fi-page-sub-navigation-dropdown {
            display: none !important;
        }
    </style>
@endif

@if ($showIrTabs)
    <style>
        /* ── Sub-navigation slot (matches fi-page-sub-navigation-tabs placement) ── */
        .ir-tabs-outer {
            display: flex;
            justify-content: center;
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            scrollbar-width: none;
        }

        .ir-tabs-outer::-webkit-scrollbar { display: none; }

        /* ── Bar container, matches Filament fi-tabs (white pill) ── */
        .ir-tabs {
            display: inline-flex;
            align-items: stretch;
            gap: 0.25rem;
            padding: 0.5rem;
            background-color: #fff;
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            outline: 1px solid rgb(3 7 18 / 0.05);
            overflow: visible;
            flex-shrink: 0;
        }

        /* ── Each top-level item wrapper ── */
        .ir-tabs__entry {
            position: relative;
            display: flex;
        }

        /* ── Shared style for both <a> links and <button> triggers ── */
        .ir-tabs__item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.5rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            line-height: 1.25rem;
            color: rgb(107 114 128);
            white-space: nowrap;
            text-decoration: none;
            background: none;
            border: none;
            cursor: pointer;
            transition: background-color 75ms ease, color 75ms ease;
        }

        .ir-tabs__item:hover {
            background-color: rgb(249 250 251);
            color: rgb(55 65 81);
        }

        .ir-tabs__item:focus-visible {
            background-color: rgb(249 250 251);
            outline: none;
        }

        .ir-tabs__item--active {
            background-color: rgb(249 250 251);
            color: var(--mtb-primary-dark);
        }

        /* ── Leading icon ── */
        .ir-tabs__icon {
            width: 1rem;
            height: 1rem;
            flex-shrink: 0;
            stroke: currentColor;
            fill: none;
            color: rgb(156 163 175);
        }

        .ir-tabs__item--active .ir-tabs__icon {
            color: var(--mtb-primary-dark);
        }

        .ir-tabs__item:hover .ir-tabs__icon {
            color: rgb(55 65 81);
        }

        .ir-tabs__item--active:hover .ir-tabs__icon {
            color: var(--mtb-primary-dark);
        }

        /* ── Trailing chevron on dropdown triggers ── */
        .ir-tabs__chevron {
            width: 0.75rem;
            height: 0.75rem;
            color: rgb(156 163 175);
            transition: transform 150ms ease;
            flex-shrink: 0;
        }

        .ir-tabs__entry[data-open] .ir-tabs__chevron {
            transform: rotate(180deg);
        }

        /* ── Dropdown panel, position:fixed escapes any parent overflow ── */
        .ir-tabs__dropdown {
            display: none;
            position: fixed;
            z-index: 9999;
            min-width: 13rem;
            padding: 0.3rem;
            background-color: #fff;
            border: 1px solid var(--mtb-line);
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        .ir-tabs__entry[data-open] .ir-tabs__dropdown {
            display: block;
        }

        /* ── Dropdown items ── */
        .ir-tabs__dropdown-item {
            display: block;
            border-radius: 0.5rem;
            padding: 0.45rem 0.85rem;
            font-size: 0.875rem;
            font-weight: 400;
            color: rgb(107 114 128);
            text-decoration: none;
            transition: background-color 100ms ease, color 100ms ease;
            white-space: nowrap;
        }

        .ir-tabs__dropdown-item:hover {
            background-color: rgb(249 250 251);
            color: rgb(55 65 81);
        }

        .ir-tabs__dropdown-item--active {
            background-color: rgb(249 250 251);
            color: var(--mtb-primary-dark);
            font-weight: 500;
        }

        /* ── Dark mode parity with fi-tabs ── */
        .dark .ir-tabs {
            background-color: rgb(17 24 39);
            outline-color: rgb(255 255 255 / 0.1);
        }

        .dark .ir-tabs__item {
            color: rgb(156 163 175);
        }

        .dark .ir-tabs__item:hover,
        .dark .ir-tabs__item:focus-visible,
        .dark .ir-tabs__item--active {
            background-color: rgb(255 255 255 / 0.05);
        }

        .dark .ir-tabs__item:hover {
            color: rgb(229 231 235);
        }

        .dark .ir-tabs__item--active {
            color: rgb(74 222 128);
        }

        .dark .ir-tabs__icon,
        .dark .ir-tabs__chevron {
            color: rgb(107 114 128);
        }

        .dark .ir-tabs__item--active .ir-tabs__icon {
            color: rgb(74 222 128);
        }

        .dark .ir-tabs__dropdown {
            background-color: rgb(17 24 39);
            border-color: rgb(51 65 85);
        }

        .dark .ir-tabs__dropdown-item {
            color: rgb(156 163 175);
        }

        .dark .ir-tabs__dropdown-item:hover {
            background-color: rgb(255 255 255 / 0.05);
            color: rgb(229 231 235);
        }

        .dark .ir-tabs__dropdown-item--active {
            background-color: rgb(255 255 255 / 0.05);
            color: rgb(74 222 128);
        }

        /* ── Mobile: compact section picker instead of horizontal tabs ── */
        .ir-tabs-mobile {
            display: none;
            width: 100%;
            padding: 0 0.25rem;
        }

        .ir-tabs-mobile__label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: rgb(107 114 128);
        }

        .ir-tabs-mobile__select {
            width: 100%;
            border: 1px solid var(--mtb-line);
            border-radius: 0.65rem;
            background: #fff;
            color: rgb(31 41 55);
            font-size: 0.9375rem;
            font-weight: 500;
            padding: 0.65rem 2.25rem 0.65rem 0.85rem;
            min-height: 44px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.65rem center;
            background-size: 1.1rem;
        }

        .dark .ir-tabs-mobile__select {
            background-color: rgb(17 24 39);
            border-color: rgb(51 65 85);
            color: rgb(229 231 235);
        }

        @media (max-width: 768px) {
            .ir-tabs-outer {
                display: none;
            }

            .ir-tabs-mobile {
                display: block;
            }
        }

    </style>

    <div class="ir-tabs-mobile" data-ir-quick-access>
        <label class="ir-tabs-mobile__label" for="ir-tabs-mobile-select">Impact Rent section</label>
        <select
            id="ir-tabs-mobile-select"
            class="ir-tabs-mobile__select"
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

    <div class="ir-tabs-outer" data-ir-quick-access>
        <nav class="ir-tabs" aria-label="Impact Rent navigation">
            @foreach ($quickLinks as $link)
                <div class="ir-tabs__entry">

                    @if (!empty($link['items']))
                        {{-- Dropdown trigger --}}
                        <button
                            type="button"
                            class="ir-tabs__item {{ $link['active'] ? 'ir-tabs__item--active' : '' }}"
                            aria-haspopup="true"
                            aria-expanded="false"
                            data-ir-trigger
                        >
                            <svg class="ir-tabs__icon" viewBox="0 0 24 24" aria-hidden="true">
                                {!! $link['icon'] !!}
                            </svg>
                            <span>{{ $link['label'] }}</span>
                            <svg class="ir-tabs__chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div class="ir-tabs__dropdown" role="menu">
                            @foreach ($link['items'] as $sub)
                                <a
                                    href="{{ $sub['url'] }}"
                                    role="menuitem"
                                    class="ir-tabs__dropdown-item {{ ($sub['active'] ?? false) ? 'ir-tabs__dropdown-item--active' : '' }}"
                                    @if (($sub['active'] ?? false)) aria-current="page" @endif
                                >
                                    {{ $sub['label'] }}
                                </a>
                            @endforeach
                        </div>

                    @else
                        {{-- Direct link --}}
                        <a
                            href="{{ $link['url'] }}"
                            class="ir-tabs__item {{ $link['active'] ? 'ir-tabs__item--active' : '' }}"
                            @if ($link['active']) aria-current="page" @endif
                        >
                            <svg class="ir-tabs__icon" viewBox="0 0 24 24" aria-hidden="true">
                                {!! $link['icon'] !!}
                            </svg>
                            <span>{{ $link['label'] }}</span>
                        </a>
                    @endif

                </div>
            @endforeach
        </nav>
    </div>

    <script>
    (function () {
        if (window.__irTabsInitialised) return;
        window.__irTabsInitialised = true;

        function positionDropdown(trigger, dropdown) {
            const rect = trigger.getBoundingClientRect();
            // Anchor: 6px below the trigger, horizontally centered on the trigger
            dropdown.style.top = (rect.bottom + 6) + 'px';
            dropdown.style.left = (rect.left + rect.width / 2) + 'px';
            dropdown.style.transform = 'translateX(-50%)';

            // Keep it on-screen if it would overflow the viewport edges
            requestAnimationFrame(() => {
                const ddRect = dropdown.getBoundingClientRect();
                const margin = 8;
                if (ddRect.right > window.innerWidth - margin) {
                    const overflow = ddRect.right - (window.innerWidth - margin);
                    dropdown.style.left = (rect.left + rect.width / 2 - overflow) + 'px';
                }
                if (ddRect.left < margin) {
                    dropdown.style.left = (margin + ddRect.width / 2) + 'px';
                }
            });
        }

        function closeAll(exceptEntry) {
            document.querySelectorAll('.ir-tabs__entry[data-open]').forEach((el) => {
                if (el !== exceptEntry) {
                    el.removeAttribute('data-open');
                    const btn = el.querySelector('[data-ir-trigger]');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                }
            });
        }

        document.addEventListener('click', function (e) {
            const trigger = e.target.closest('[data-ir-trigger]');

            if (trigger) {
                e.preventDefault();
                e.stopPropagation();

                const entry = trigger.closest('.ir-tabs__entry');
                if (!entry) return;

                const dropdown = entry.querySelector('.ir-tabs__dropdown');
                const isOpen = entry.hasAttribute('data-open');
                closeAll(entry);

                if (isOpen) {
                    entry.removeAttribute('data-open');
                    trigger.setAttribute('aria-expanded', 'false');
                } else {
                    entry.setAttribute('data-open', '');
                    trigger.setAttribute('aria-expanded', 'true');
                    if (dropdown) positionDropdown(trigger, dropdown);
                }
                return;
            }

            if (e.target.closest('.ir-tabs__dropdown')) return;
            closeAll(null);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeAll(null);
        });

        // Reposition open dropdown on window resize / scroll so it stays anchored
        function repositionOpen() {
            document.querySelectorAll('.ir-tabs__entry[data-open]').forEach((entry) => {
                const trigger = entry.querySelector('[data-ir-trigger]');
                const dropdown = entry.querySelector('.ir-tabs__dropdown');
                if (trigger && dropdown) positionDropdown(trigger, dropdown);
            });
        }
        window.addEventListener('resize', repositionOpen);
        window.addEventListener('scroll', repositionOpen, true);
    })();
    </script>
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
            return;
        }

        document.querySelectorAll('[data-ir-quick-access]').forEach(function (el) {
            el.remove();
        });
    });
})();
</script>
