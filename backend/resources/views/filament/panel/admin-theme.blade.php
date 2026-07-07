<style>
    :root {
        --mtb-primary: #334e68;
        --mtb-primary-dark: #243b53;
        --mtb-primary-light: #486581;
        --mtb-green: #45a06a;
        --mtb-green-dark: #3a8d5d;
        --mtb-bg: #e8edf2;
        --mtb-surface: #ffffff;
        --mtb-line: #c5d0dc;
        --mtb-text: #1a2332;
        --mtb-muted: #627d98;
        /* legacy aliases */
        --mtb-navy: var(--mtb-primary);
    }

    .fi-body {
        background-color: var(--mtb-bg);
        color: var(--mtb-text);
    }

    .fi-layout,
    .fi-main-ctn,
    .fi-main,
    .fi-page {
        background-color: var(--mtb-bg);
    }

    /* ── Sidebar ─────────────────────────────────────────────────────────── */
    .fi-sidebar,
    .fi-main-sidebar {
        background-color: var(--mtb-primary) !important;
        border-right: 1px solid var(--mtb-primary-dark);
    }

    .fi-main-sidebar {
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .fi-sidebar-nav {
        flex: 1 1 auto;
        min-height: 0;
        overflow-x: hidden;
        overflow-y: auto;
        scrollbar-gutter: stable;
        scrollbar-width: thin;
        scrollbar-color: rgb(255 255 255 / 0.42) rgb(255 255 255 / 0.08);
    }

    .fi-sidebar-nav::-webkit-scrollbar {
        width: 8px;
    }

    .fi-sidebar-nav::-webkit-scrollbar-track {
        background: rgb(255 255 255 / 0.08);
        border-radius: 999px;
    }

    .fi-sidebar-nav::-webkit-scrollbar-thumb {
        background-color: rgb(255 255 255 / 0.38);
        border-radius: 999px;
        border: 2px solid transparent;
        background-clip: padding-box;
    }

    .fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
        background-color: rgb(255 255 255 / 0.58);
    }

    .fi-sidebar-header {
        background-color: var(--mtb-primary) !important;
        border-bottom: 1px solid rgb(255 255 255 / 0.1);
    }

    .fi-body-has-topbar .fi-sidebar-header {
        background-color: var(--mtb-primary) !important;
    }

    /* Labels — override Filament text-gray-* utilities on dark sidebar */
    .fi-sidebar .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-database-notifications-btn-label {
        color: rgb(255 255 255 / 0.88) !important;
    }

    .fi-sidebar .fi-sidebar-group-label {
        color: rgb(255 255 255 / 0.5) !important;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-size: 0.6875rem;
    }

    .fi-sidebar .fi-sidebar-item-btn > .fi-icon,
    .fi-sidebar .fi-sidebar-group-btn > .fi-icon,
    .fi-sidebar .fi-sidebar-group-dropdown-trigger-btn > .fi-icon,
    .fi-sidebar .fi-sidebar-database-notifications-btn > .fi-icon {
        color: rgb(255 255 255 / 0.55) !important;
    }

    .fi-sidebar .fi-sidebar-item-btn:hover,
    .fi-sidebar .fi-sidebar-item-btn:focus-visible,
    .fi-sidebar .fi-sidebar-group-btn:hover,
    .fi-sidebar .fi-sidebar-group-btn:focus-visible,
    .fi-sidebar .fi-sidebar-group-dropdown-trigger-btn:hover,
    .fi-sidebar .fi-sidebar-group-dropdown-trigger-btn:focus-visible,
    .fi-sidebar .fi-sidebar-database-notifications-btn:hover,
    .fi-sidebar .fi-sidebar-database-notifications-btn:focus-visible {
        background-color: rgb(255 255 255 / 0.08) !important;
    }

    .fi-sidebar .fi-sidebar-item-btn:hover .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-item-btn:focus-visible .fi-sidebar-item-label {
        color: #fff !important;
    }

    .fi-sidebar .fi-sidebar-item-btn:hover > .fi-icon,
    .fi-sidebar .fi-sidebar-item-btn:focus-visible > .fi-icon,
    .fi-sidebar .fi-sidebar-group-btn:hover > .fi-icon,
    .fi-sidebar .fi-sidebar-group-btn:focus-visible > .fi-icon {
        color: rgb(255 255 255 / 0.9) !important;
    }

    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn,
    .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn {
        background-color: rgb(255 255 255 / 0.14) !important;
    }

    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-sidebar-item-label,
    .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn .fi-sidebar-item-label {
        color: #fff !important;
        font-weight: 600;
    }

    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-icon,
    .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-active-child-items > .fi-sidebar-item-btn > .fi-icon {
        color: #fff !important;
    }

    .fi-sidebar .fi-sidebar-group.fi-active .fi-sidebar-group-label,
    .fi-sidebar .fi-sidebar-group.fi-active .fi-sidebar-group-btn > .fi-icon {
        color: rgb(255 255 255 / 0.9) !important;
    }

    .fi-sidebar .fi-sidebar-item-grouped-border-part {
        background-color: rgb(255 255 255 / 0.35) !important;
    }

    .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-grouped-border-part,
    .fi-sidebar .fi-sidebar-item.fi-sidebar-item-has-active-child-items .fi-sidebar-item-grouped-border-part {
        background-color: #fff !important;
    }

    .fi-sidebar .fi-sidebar-item-grouped-border-part-not-first,
    .fi-sidebar .fi-sidebar-item-grouped-border-part-not-last {
        background-color: rgb(255 255 255 / 0.2) !important;
    }

    .fi-sidebar .fi-sidebar-close-collapse-sidebar-btn,
    .fi-sidebar .fi-sidebar-open-collapse-sidebar-btn {
        color: rgb(255 255 255 / 0.55) !important;
    }

    .fi-sidebar .fi-sidebar-close-collapse-sidebar-btn:hover,
    .fi-sidebar .fi-sidebar-close-collapse-sidebar-btn:focus-visible,
    .fi-sidebar .fi-sidebar-open-collapse-sidebar-btn:hover,
    .fi-sidebar .fi-sidebar-open-collapse-sidebar-btn:focus-visible {
        color: #fff !important;
        background-color: rgb(255 255 255 / 0.08) !important;
    }

    .fi-sidebar .fi-badge {
        background-color: rgb(255 255 255 / 0.15) !important;
        color: #fff !important;
        border-color: rgb(255 255 255 / 0.2) !important;
    }

    @media (min-width: 1024px) {
        .fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) {
            width: 4.75rem;
            min-width: 4.75rem;
        }

        .fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav {
            padding-inline: 0.875rem;
            padding-block: 1.5rem;
        }

        .fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-nav-groups {
            gap: 0.875rem;
        }

        .fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-items {
            gap: 0.5rem;
        }

        .fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-btn,
        .fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-dropdown-trigger-btn {
            padding: 0.625rem;
            width: 100%;
        }

        .fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-item-btn .fi-icon.fi-size-lg,
        .fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-group-dropdown-trigger-btn .fi-icon.fi-size-lg {
            width: 1.375rem;
            height: 1.375rem;
        }
    }

    /* ── Topbar ──────────────────────────────────────────────────────────── */
    .fi-topbar {
        background-color: var(--mtb-surface);
        border-bottom: 1px solid var(--mtb-line);
        box-shadow: 0 1px 3px rgb(51 78 104 / 0.08);
    }

    .fi-topbar-item-btn {
        color: var(--mtb-primary);
    }

    /* ── Cards & sections ────────────────────────────────────────────────── */
    .fi-section,
    .fi-wi-stats-overview-stat,
    .fi-ta-ctn {
        border-color: var(--mtb-line);
        box-shadow: 0 1px 2px rgb(51 78 104 / 0.05);
    }

    .fi-section-content-ctn {
        background-color: var(--mtb-surface);
    }

    .fi-wi-stats-overview-stat {
        background-color: var(--mtb-surface);
    }

    /* ── Primary buttons (dark brand color needs white label text) ───────── */
    .fi-btn.fi-color-primary:not(.fi-outlined):not(label),
    a.fi-btn.fi-color-primary:not(.fi-outlined) {
        --bg: var(--mtb-primary) !important;
        --hover-bg: var(--mtb-primary-dark) !important;
        --text: #fff !important;
        --hover-text: #fff !important;
        --dark-bg: var(--mtb-primary-dark) !important;
        --dark-hover-bg: var(--mtb-primary) !important;
        --dark-text: #fff !important;
        --dark-hover-text: #fff !important;
    }

    .fi-btn.fi-color-primary:not(.fi-outlined):not(label) > .fi-icon,
    a.fi-btn.fi-color-primary:not(.fi-outlined) > .fi-icon {
        color: #fff !important;
    }

    /* ── Header actions ──────────────────────────────────────────────────── */
    .fi-header-actions-ctn .fi-ac-btn-action.fi-outlined,
    .fi-header-actions-ctn .fi-ac-btn-action.fi-btn-variant-outline {
        background: var(--mtb-surface);
        border-color: var(--mtb-line);
        color: var(--mtb-primary);
        box-shadow: none;
    }

    .fi-header-actions-ctn .fi-ac-btn-action.fi-outlined:hover,
    .fi-header-actions-ctn .fi-ac-btn-action.fi-outlined:focus-visible,
    .fi-header-actions-ctn .fi-ac-btn-action.fi-btn-variant-outline:hover,
    .fi-header-actions-ctn .fi-ac-btn-action.fi-btn-variant-outline:focus-visible {
        background: var(--mtb-bg);
        border-color: var(--mtb-primary-light);
        color: var(--mtb-primary-dark);
    }

    .fi-header-actions-ctn .fi-ac-btn-action .fi-badge {
        font-size: 0.6875rem;
        font-weight: 700;
        min-width: 1.25rem;
        padding-inline: 0.375rem;
    }

    /* ── Brand logo ──────────────────────────────────────────────────────── */
    .tb-admin-brand-logo {
        display: inline-flex;
        align-items: center;
        gap: 0.625rem;
        font-family: 'Quicksand', 'Open Sans', system-ui, sans-serif;
        font-weight: 700;
        font-size: 1.25rem;
        letter-spacing: -0.02em;
        color: #fff;
        line-height: 1;
    }

    .tb-admin-brand-logo__mark {
        display: grid;
        place-items: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.625rem;
        background: rgb(255 255 255 / 0.15);
        color: #fff;
        flex-shrink: 0;
    }

    .tb-admin-brand-logo__mark svg {
        width: 1.125rem;
        height: 1.125rem;
    }

    .tb-admin-brand-logo__accent {
        color: rgb(255 255 255 / 0.75);
    }

    .tb-admin-brand-logo__image {
        display: block;
        height: 2.25rem;
        width: auto;
        max-width: min(220px, 42vw);
        object-fit: contain;
        filter: brightness(0) invert(1);
    }

    /* ── Page headings ───────────────────────────────────────────────────── */
    .fi-header-heading {
        color: var(--mtb-primary-dark);
    }

    .fi-header-subheading {
        color: var(--mtb-muted);
    }

    /* ── Dark mode ───────────────────────────────────────────────────────── */
    .dark .fi-body,
    .dark .fi-layout,
    .dark .fi-main-ctn,
    .dark .fi-main,
    .dark .fi-page {
        background-color: rgb(15 23 42);
    }

    .dark .fi-sidebar {
        background-color: rgb(15 23 42);
        border-color: rgb(36 59 83);
    }

    .dark .fi-sidebar-header {
        background-color: rgb(15 23 42);
        border-color: rgb(51 65 85);
    }

    .dark .fi-topbar {
        background-color: rgb(15 23 42);
        border-color: rgb(51 65 85);
        box-shadow: none;
    }

    /* ── Table badge chips ───────────────────────────────────────────────── */
    .fi-ta-table .fi-badge {
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.01em;
        text-transform: capitalize;
        box-shadow: none;
        border: 1px solid var(--mtb-line);
        background-color: #dce4ec;
        color: var(--mtb-primary-dark);
    }

    .fi-ta-table .fi-badge.fi-color-gray,
    .fi-ta-table .fi-badge.fi-color-primary {
        background-color: #dce4ec;
        border-color: var(--mtb-line);
        color: var(--mtb-primary-dark);
    }

    .fi-ta-table .fi-badge.fi-color-success {
        background-color: #e7f3ec;
        border-color: rgb(58 141 93 / 0.25);
        color: #3a8d5d;
    }

    .fi-ta-table .fi-badge.fi-color-warning {
        background-color: #fef3c7;
        border-color: rgb(180 83 9 / 0.2);
        color: #b45309;
    }

    .fi-ta-table .fi-badge.fi-color-danger {
        background-color: #fee2e2;
        border-color: rgb(185 28 28 / 0.2);
        color: #b91c1c;
    }

    .fi-ta-table .fi-badge.fi-color-info {
        background-color: #dbeafe;
        border-color: rgb(51 78 104 / 0.25);
        color: var(--mtb-primary);
    }

    .dark .fi-ta-table .fi-badge {
        border-color: rgb(51 65 85);
        background-color: rgb(30 41 59);
        color: rgb(226 232 240);
    }

    .dark .fi-ta-table .fi-badge.fi-color-gray,
    .dark .fi-ta-table .fi-badge.fi-color-primary {
        background-color: rgb(30 41 59);
        border-color: rgb(51 65 85);
        color: rgb(226 232 240);
    }

    .dark .fi-ta-table .fi-badge.fi-color-success {
        background-color: rgb(22 50 36);
        border-color: rgb(58 141 93 / 0.35);
        color: #6ecf96;
    }

    .dark .fi-ta-table .fi-badge.fi-color-warning {
        background-color: rgb(60 45 20);
        border-color: rgb(180 83 9 / 0.35);
        color: #fbbf24;
    }

    .dark .fi-ta-table .fi-badge.fi-color-danger {
        background-color: rgb(60 25 25);
        border-color: rgb(185 28 28 / 0.35);
        color: #f87171;
    }

    .dark .fi-ta-table .fi-badge.fi-color-info {
        background-color: rgb(20 40 60);
        border-color: rgb(51 78 104 / 0.35);
        color: #93c5fd;
    }

    /* ── Mobile admin UX ─────────────────────────────────────────────────── */
    @media (max-width: 768px) {
        .fi-main {
            padding-inline: 0.75rem;
        }

        .fi-page-main {
            gap: 1rem;
        }

        .fi-header-heading {
            font-size: 1.125rem;
            line-height: 1.35;
        }

        .fi-page-header-main-ctn {
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .fi-page-header-actions {
            flex-wrap: wrap;
            gap: 0.5rem;
            width: 100%;
        }

        .fi-ac {
            flex-wrap: wrap;
        }

        .fi-ta-ctn,
        .fi-ta-content-ctn {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .fi-ta-table {
            min-width: 36rem;
        }

        .fi-fo-field-wrp-label span {
            font-size: 0.875rem;
        }

        .fi-form-actions {
            flex-direction: column;
            align-items: stretch;
            gap: 0.5rem;
        }

        .fi-form-actions .fi-btn {
            width: 100%;
            justify-content: center;
        }

        .fi-section-header {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .fi-modal-window {
            width: calc(100vw - 1.5rem) !important;
            max-width: calc(100vw - 1.5rem) !important;
            margin-inline: 0.75rem;
        }

        /* Dashboard widgets */
        .fi-page-main .fi-wi-widget.fi-grid-col {
            grid-column: 1 / -1;
            width: 100%;
            min-width: 0;
        }

        .fi-wi-stats-overview-stat {
            min-width: 0;
            padding: 1rem;
        }

        .fi-wi-stats-overview-stat-value {
            font-size: 1.5rem;
            line-height: 1.15;
            white-space: nowrap;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .fi-wi-stats-overview-stat-value::-webkit-scrollbar {
            display: none;
        }

        .fi-wi-stats-overview-stat-description {
            line-height: 1.35;
            flex-wrap: wrap;
        }

        .fi-wi-chart.fi-wi-widget {
            overflow: hidden;
        }

        .fi-wi-chart .fi-section-content-ctn {
            padding-inline: 0.75rem;
        }

        .fi-wi-chart-canvas-ctn {
            position: relative;
            width: 100%;
            min-height: 16rem;
        }

        .fi-wi-chart canvas {
            width: 100% !important;
            max-height: 16rem !important;
        }

        .fi-wi-table .fi-section-header {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .fi-wi-table .fi-ta-ctn {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .fi-wi-table .fi-ta-table {
            min-width: 0;
            width: 100%;
        }

        .fi-page-header-widgets {
            gap: 1rem;
        }
    }
</style>
