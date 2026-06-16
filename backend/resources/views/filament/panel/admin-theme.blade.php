<style>
    :root {
        --mtb-navy: #0f2036;
        --mtb-green: #45a06a;
        --mtb-green-dark: #3a8d5d;
        --mtb-bg: #f4f7fb;
        --mtb-line: #e2e7ef;
    }

    .fi-body {
        background-color: var(--mtb-bg);
    }

    .fi-layout,
    .fi-main-ctn,
    .fi-main,
    .fi-page {
        background-color: var(--mtb-bg);
    }

    .fi-sidebar {
        background-color: #fff;
        border-right: 1px solid var(--mtb-line);
    }

    .fi-sidebar-header {
        background-color: #fff;
        border-bottom: 1px solid var(--mtb-line);
    }

    .fi-topbar {
        background-color: #fff;
        border-bottom: 1px solid var(--mtb-line);
        box-shadow: none;
    }

    .fi-sidebar-item-btn:hover,
    .fi-sidebar-item-btn:focus-visible {
        background-color: rgb(69 160 106 / 0.06);
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background-color: rgb(69 160 106 / 0.1);
        color: var(--mtb-green-dark);
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn .fi-icon {
        color: var(--mtb-green);
    }

    .fi-sidebar-group-label {
        color: rgb(90 107 130);
    }

    .fi-section,
    .fi-wi-stats-overview-stat,
    .fi-ta-ctn {
        border-color: var(--mtb-line);
    }

    .fi-header-actions-ctn .fi-ac-btn-action.fi-btn-variant-outline {
        background: #fff;
        border-color: var(--mtb-line);
        color: var(--mtb-navy);
        box-shadow: none;
    }

    .fi-header-actions-ctn .fi-ac-btn-action.fi-btn-variant-outline:hover,
    .fi-header-actions-ctn .fi-ac-btn-action.fi-btn-variant-outline:focus-visible {
        background: var(--mtb-bg);
        border-color: rgb(69 160 106 / 0.35);
        color: var(--mtb-green-dark);
    }

    .fi-header-actions-ctn .fi-ac-btn-action .fi-badge {
        font-size: 0.6875rem;
        font-weight: 700;
        min-width: 1.25rem;
        padding-inline: 0.375rem;
    }

    .fi-header-actions-ctn .fi-ac-btn-action.fi-btn-variant-filled.fi-color-primary {
        background: var(--mtb-green);
        border-color: var(--mtb-green);
        color: #fff;
    }

    .fi-header-actions-ctn .fi-ac-btn-action.fi-btn-variant-filled.fi-color-primary:hover,
    .fi-header-actions-ctn .fi-ac-btn-action.fi-btn-variant-filled.fi-color-primary:focus-visible {
        background: var(--mtb-green-dark);
        border-color: var(--mtb-green-dark);
    }

    .tb-admin-brand-logo {
        display: inline-flex;
        align-items: center;
        gap: 0.625rem;
        font-family: 'Quicksand', 'Open Sans', system-ui, sans-serif;
        font-weight: 700;
        font-size: 1.25rem;
        letter-spacing: -0.02em;
        color: var(--mtb-navy);
        line-height: 1;
    }

    .tb-admin-brand-logo__mark {
        display: grid;
        place-items: center;
        width: 2rem;
        height: 2rem;
        border-radius: 0.625rem;
        background: linear-gradient(150deg, var(--mtb-green), #5bb481);
        color: #fff;
        flex-shrink: 0;
    }

    .tb-admin-brand-logo__mark svg {
        width: 1.125rem;
        height: 1.125rem;
    }

    .tb-admin-brand-logo__accent {
        color: var(--mtb-green);
    }

    .tb-admin-brand-logo__image {
        display: block;
        height: 2.25rem;
        width: auto;
        max-width: min(220px, 42vw);
        object-fit: contain;
    }

    .dark .fi-body,
    .dark .fi-layout,
    .dark .fi-main-ctn,
    .dark .fi-main,
    .dark .fi-page {
        background-color: rgb(15 23 42);
    }

    .dark .fi-sidebar,
    .dark .fi-sidebar-header,
    .dark .fi-topbar {
        background-color: rgb(15 23 42);
        border-color: rgb(51 65 85);
    }

    /* Table badge chips, neutral labels + soft semantic status tints */
    .fi-ta-table .fi-badge {
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.01em;
        text-transform: capitalize;
        box-shadow: none;
        border: 1px solid var(--mtb-line);
        background-color: #eef2f8;
        color: var(--mtb-navy);
    }

    .fi-ta-table .fi-badge.fi-color-gray,
    .fi-ta-table .fi-badge.fi-color-primary {
        background-color: #eef2f8;
        border-color: var(--mtb-line);
        color: var(--mtb-navy);
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
        background-color: #e8f2fb;
        border-color: rgb(15 112 184 / 0.2);
        color: #0f70b8;
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
        border-color: rgb(15 112 184 / 0.35);
        color: #60a5fa;
    }
</style>
