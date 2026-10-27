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
        'vehicles' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M8.25 18.75a1.5 1.5 0 0 1-3 0M19.5 18.75a1.5 1.5 0 1 1-3 0m-9.75 0H4.875a1.125 1.125 0 0 1-1.125-1.125V14.25M19.5 12.75v4.875c0 .621-.504 1.125-1.125 1.125h-1.875M19.5 12.75H4.5M19.5 12.75 18.4 7.05a1.125 1.125 0 0 0-1.106-.925H6.706c-.553 0-1.027.4-1.106.945L4.5 12.75"/>',
        'pricing'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6 6h.008v.008H6V6Z"/>',
        'bookings' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v11.25A1.5 1.5 0 0 1 19.5 21h-15A1.5 1.5 0 0 1 3 19.5V8.25A1.5 1.5 0 0 1 4.5 6.75Z"/>',
        'finance'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
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
                ['label' => 'All vehicles',      'url' => url('/admin/impact-rent/cars'),               'active' => request()->is('admin/impact-rent/cars', 'admin/impact-rent/cars/')],
                ['label' => 'Fleet units',       'url' => url('/admin/impact-rent/car-units'),          'active' => request()->is('admin/impact-rent/car-units*')],
                ['label' => 'Main Categories',   'url' => url('/admin/impact-rent/main-categories'),    'active' => request()->is('admin/impact-rent/main-categories*')],
                ['label' => 'Sub Categories',    'url' => url('/admin/impact-rent/sub-categories'),     'active' => request()->is('admin/impact-rent/sub-categories*')],
                ['label' => 'Features',          'url' => url('/admin/impact-rent/characteristics'),    'active' => request()->is('admin/impact-rent/characteristics*')],
                ['label' => 'Locations',         'url' => url('/admin/impact-rent/locations'),          'active' => request()->is('admin/impact-rent/locations*')],
                ['label' => 'Rental Conditions', 'url' => url('/admin/impact-rent/rental-conditions'),  'active' => request()->is('admin/impact-rent/rental-conditions*')],
            ],
        ],
        [
            'label'  => 'Pricing',
            'icon'   => $icons['pricing'],
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
                ['label' => 'Pricing rules',     'url' => url('/admin/impact-rent/special-prices'),     'active' => request()->is('admin/impact-rent/special-prices*')],
                ['label' => 'Base prices',       'url' => url('/admin/impact-rent/daily-fares'),        'active' => request()->is('admin/impact-rent/daily-fares*')],
                ['label' => 'Extra hour fares',  'url' => url('/admin/impact-rent/extra-hour-fares'),   'active' => request()->is('admin/impact-rent/extra-hour-fares*')],
                ['label' => 'Hourly fares',      'url' => url('/admin/impact-rent/hourly-fares'),       'active' => request()->is('admin/impact-rent/hourly-fares*')],
                ['label' => 'Out-of-hours fees', 'url' => url('/admin/impact-rent/out-of-hours-fees'),  'active' => request()->is('admin/impact-rent/out-of-hours-fees*')],
                ['label' => 'Location fees',     'url' => url('/admin/impact-rent/location-fees'),      'active' => request()->is('admin/impact-rent/location-fees*')],
                ['label' => 'Add-ons',           'url' => url('/admin/impact-rent/rental-options'),     'active' => request()->is('admin/impact-rent/rental-options*')],
                ['label' => 'Discount codes',    'url' => url('/admin/impact-rent/coupons'),            'active' => request()->is('admin/impact-rent/coupons*')],
                ['label' => 'Tax Rates',         'url' => url('/admin/impact-rent/tax-rates'),          'active' => request()->is('admin/impact-rent/tax-rates*')],
                ['label' => 'Types of Price',    'url' => url('/admin/impact-rent/price-types'),        'active' => request()->is('admin/impact-rent/price-types*')],
                ['label' => 'Fares Overview',    'url' => url('/admin/impact-rent/fares-overview'),     'active' => request()->is('admin/impact-rent/fares-overview*')],
            ],
        ],
        [
            'label'  => 'Bookings',
            'icon'   => $icons['bookings'],
            'active' => request()->is('admin/impact-rent/orders-calendar*')
                || request()->is('admin/impact-rent/orders')
                || request()->is('admin/impact-rent/orders/*')
                || request()->is('admin/impact-rent/booking-restrictions*')
                || request()->is('admin/registered-clients*'),
            'items'  => [
                ['label' => 'Calendar',      'url' => url('/admin/impact-rent/orders-calendar'),     'active' => request()->is('admin/impact-rent/orders-calendar*')],
                ['label' => 'Reservations',  'url' => url('/admin/impact-rent/orders'),              'active' => request()->is('admin/impact-rent/orders') || request()->is('admin/impact-rent/orders/*')],
                ['label' => 'Restrictions',  'url' => url('/admin/impact-rent/booking-restrictions'), 'active' => request()->is('admin/impact-rent/booking-restrictions*')],
                ['label' => 'Customers',     'url' => url('/admin/registered-clients'),              'active' => request()->is('admin/registered-clients*')],
            ],
        ],
        [
            'label'  => 'Finance',
            'icon'   => $icons['finance'],
            'active' => request()->is('admin/impact-rent/reports*')
                || request()->is('admin/impact-rent/tracking-statistics*')
                || request()->is('admin/impact-rent/tracking-campaigns*'),
            'items'  => [
                ['label' => 'Reports',              'url' => url('/admin/impact-rent/reports'),              'active' => request()->is('admin/impact-rent/reports*')],
                ['label' => 'Statistics Tracking',  'url' => url('/admin/impact-rent/tracking-statistics'),  'active' => request()->is('admin/impact-rent/tracking-statistics*')],
                ['label' => 'Tracking campaigns',   'url' => url('/admin/impact-rent/tracking-campaigns'),   'active' => request()->is('admin/impact-rent/tracking-campaigns*')],
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

        html.ir-shell {
            background: #f4f6f9;
        }

        html.ir-shell .fi-main,
        html.ir-shell .fi-body {
            background: #f4f6f9;
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

        html.ir-shell .fi-main {
            background: #f4f6f9;
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

        html.ir-shell .fi-ta-ctn,
        html.ir-shell .fi-section,
        html.ir-shell .fi-wi-stats-overview-stat,
        html.ir-shell .fi-sc-section {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
        }

        html.ir-shell .fi-ta-ctn {
            overflow: hidden;
        }

        html.ir-shell .fi-ta-header-toolbar {
            background: #fff;
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
            background: #059669;
            color: #fff;
        }

        html.ir-shell .fi-badge.fi-color-gray,
        html.ir-shell .fi-badge.fi-color-neutral {
            background: #64748b;
            color: #fff;
        }

        html.ir-shell .fi-badge.fi-color-warning {
            background: #f59e0b;
            color: #fff;
        }

        html.ir-shell .fi-badge.fi-color-danger {
            background: #ef4444;
            color: #fff;
        }

        html.ir-shell .fi-badge.fi-color-info,
        html.ir-shell .fi-badge.fi-color-primary {
            background: #0164a3;
            color: #fff;
        }

        html.ir-shell .fi-input-wrp {
            border-radius: 0.5rem;
            background: #fff;
            border-color: #e2e8f0;
            box-shadow: none;
        }

        html.ir-shell .fi-input-wrp:focus-within {
            border-color: #0164a3;
            box-shadow: 0 0 0 3px color-mix(in srgb, #0164a3 18%, transparent);
        }

        html.ir-shell .fi-fo-field-wrp-label span,
        html.ir-shell .fi-sc-field-wrp-label span {
            font-size: 0.875rem;
            font-weight: 500;
            color: #0f172a;
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
            overflow: hidden;
        }

        html.ir-shell .fi-section-header,
        html.ir-shell .fi-sc-section-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
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

        html.ir-shell .fi-fo-field-wrp {
            gap: 0.375rem;
        }

        html.ir-shell .fi-input,
        html.ir-shell .fi-select-input,
        html.ir-shell .fi-textarea {
            min-height: 2.5rem;
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
                                {!! $link['icon'] !!}
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
