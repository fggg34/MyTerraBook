<style>
    /* ── Location form layout (scoped to create/edit pages only) ─────────── */
    .ir-location-form-page .fi-main {
        max-width: none;
        width: 100%;
    }

    .ir-booking-restriction-form-page .fi-main {
        max-width: none;
        width: 100%;
    }

    .ir-booking-restriction-form-page .fi-page,
    .ir-booking-restriction-form-page .fi-main-ctn,
    .ir-booking-restriction-form-page .fi-page-content {
        max-width: none;
        width: 100%;
    }

    .ir-location-form-page .fi-page-content > .fi-sc-form {
        width: 100%;
        max-width: none;
    }

    .ir-booking-restriction-form-page .fi-page-content > .fi-sc-form {
        width: 100%;
        max-width: none;
    }

    .ir-location-form-page .ir-location-form-panel,
    .ir-location-form-page .ir-location-form-panel .fi-section-content {
        min-width: 0;
    }

    .ir-booking-restriction-form-page .fi-section,
    .ir-booking-restriction-form-page .fi-section-content {
        min-width: 0;
    }

    .ir-categories-page .fi-main,
    .ir-categories-page .fi-page,
    .ir-categories-page .fi-main-ctn,
    .ir-categories-page .fi-page-content,
    .ir-categories-page .fi-page-content > .fi-sc-form {
        max-width: none;
        width: 100%;
    }

    .ir-categories-page .fi-ta-table thead tr,
    .ir-categories-page .fi-ta-table tbody tr {
        border-bottom: 1px solid rgb(229 231 235 / 1);
    }

    .ir-rental-options-page .fi-main,
    .ir-rental-options-page .fi-page,
    .ir-rental-options-page .fi-main-ctn,
    .ir-rental-options-page .fi-page-content,
    .ir-rental-options-page .fi-page-content > .fi-sc-form {
        max-width: none;
        width: 100%;
    }

    .ir-rental-options-page .fi-ta-table thead tr,
    .ir-rental-options-page .fi-ta-table tbody tr {
        border-bottom: 1px solid rgb(229 231 235 / 1);
    }

    .ir-characteristics-page .fi-main,
    .ir-characteristics-page .fi-page,
    .ir-characteristics-page .fi-main-ctn,
    .ir-characteristics-page .fi-page-content,
    .ir-characteristics-page .fi-page-content > .fi-sc-form {
        max-width: none;
        width: 100%;
    }

    .ir-characteristics-page .fi-ta-table thead tr,
    .ir-characteristics-page .fi-ta-table tbody tr {
        border-bottom: 1px solid rgb(229 231 235 / 1);
    }

    .ir-cars-page .fi-main,
    .ir-cars-page .fi-page,
    .ir-cars-page .fi-main-ctn,
    .ir-cars-page .fi-page-content,
    .ir-cars-page .fi-page-content > .fi-sc-form {
        max-width: none;
        width: 100%;
    }

    .ir-cars-page .fi-ta-table thead tr,
    .ir-cars-page .fi-ta-table tbody tr {
        border-bottom: 1px solid rgb(229 231 235 / 1);
    }

    .ir-daily-fares-page .fi-main,
    .ir-daily-fares-page .fi-page,
    .ir-daily-fares-page .fi-main-ctn,
    .ir-daily-fares-page .fi-page-content,
    .ir-daily-fares-page .fi-page-content > .fi-sc-form {
        max-width: none;
        width: 100%;
    }

    .ir-daily-fares-page .fi-ta-table thead tr,
    .ir-daily-fares-page .fi-ta-table tbody tr {
        border-bottom: 1px solid rgb(229 231 235 / 1);
    }

    .ir-daily-fares-page .ir-fares-workbench {
        border: 1px solid rgb(229 231 235 / 1);
        border-radius: 0.75rem;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.08);
    }

    .dark .ir-daily-fares-page .ir-fares-workbench {
        border-color: rgb(255 255 255 / 0.12);
        background: rgb(17 24 39 / 1);
    }

    .ir-daily-fares-page .ir-fares-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 0.875rem 1rem;
        border-bottom: 1px solid rgb(229 231 235 / 1);
    }

    .dark .ir-daily-fares-page .ir-fares-tabs {
        border-bottom-color: rgb(255 255 255 / 0.12);
    }

    .ir-daily-fares-page .ir-fares-tab {
        border: 1px solid rgb(209 213 219 / 1);
        background: #fff;
        color: rgb(31 41 55 / 1);
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        padding: 0.5rem 0.75rem;
        cursor: pointer;
    }

    .ir-daily-fares-page .ir-fares-tab.is-active {
        border-color: rgb(69 160 106 / 1);
        background: rgb(69 160 106 / 0.1);
        color: rgb(58 141 93 / 1);
    }

    .ir-daily-fares-page .ir-fares-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
        padding: 1rem;
    }

    @media screen and (min-width: 1024px) {
        .ir-daily-fares-page .ir-fares-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    .ir-daily-fares-page .ir-fares-panel {
        border: 1px solid rgb(229 231 235 / 1);
        border-radius: 0.75rem;
        background: #fff;
        padding: 1rem;
    }

    .dark .ir-daily-fares-page .ir-fares-panel {
        border-color: rgb(255 255 255 / 0.12);
        background: rgb(17 24 39 / 0.7);
    }

    .ir-daily-fares-page .ir-fares-vehicle-strip {
        display: grid;
        grid-template-columns: 112px 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .ir-daily-fares-page .ir-fares-car-image {
        width: 112px;
        height: 84px;
        border: 1px solid rgb(229 231 235 / 1);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgb(107 114 128 / 1);
        font-size: 0.75rem;
        overflow: hidden;
        background: rgb(249 250 251 / 1);
    }

    .ir-daily-fares-page .ir-fares-car-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .ir-daily-fares-page .ir-fares-vehicle-fields {
        display: grid;
        gap: 0.5rem;
    }

    .ir-daily-fares-page .ir-fares-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: rgb(55 65 81 / 1);
        margin-bottom: 0.25rem;
    }

    .ir-daily-fares-page .ir-fares-select,
    .ir-daily-fares-page .ir-fares-input {
        width: 100%;
        border: 1px solid rgb(209 213 219 / 1);
        border-radius: 0.5rem;
        padding: 0.52rem 0.65rem;
        font-size: 0.875rem;
        background: #fff;
        color: rgb(17 24 39 / 1);
    }

    .dark .ir-daily-fares-page .ir-fares-select,
    .dark .ir-daily-fares-page .ir-fares-input {
        border-color: rgb(255 255 255 / 0.18);
        background: rgb(31 41 55 / 1);
        color: rgb(229 231 235 / 1);
    }

    .ir-daily-fares-page .ir-fares-editor {
        border: 1px solid rgb(229 231 235 / 1);
        border-radius: 0.75rem;
        padding: 1rem;
        display: grid;
        gap: 0.85rem;
        background: rgb(249 250 251 / 1);
    }

    .dark .ir-daily-fares-page .ir-fares-editor {
        border-color: rgb(255 255 255 / 0.12);
        background: rgb(17 24 39 / 0.6);
    }

    .ir-daily-fares-page .ir-fares-editor-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: rgb(17 24 39 / 1);
        margin: 0;
    }

    .dark .ir-daily-fares-page .ir-fares-editor-title {
        color: rgb(243 244 246 / 1);
    }

    .ir-daily-fares-page .ir-fares-help {
        margin: 0;
        font-size: 0.75rem;
        color: rgb(107 114 128 / 1);
    }

    .ir-daily-fares-page .ir-fares-fields-two {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    .ir-daily-fares-page .ir-fares-price-wrap {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 0.5rem;
        align-items: center;
    }

    .ir-daily-fares-page .ir-fares-currency {
        font-size: 0.75rem;
        color: rgb(75 85 99 / 1);
        font-weight: 600;
    }

    .ir-daily-fares-page .ir-fares-primary-btn,
    .ir-daily-fares-page .ir-fares-secondary-btn {
        border-radius: 0.5rem;
        padding: 0.45rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 700;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .ir-daily-fares-page .ir-fares-primary-btn {
        background: rgb(69 160 106 / 1);
        color: #fff;
        width: fit-content;
    }

    .ir-daily-fares-page .ir-fares-secondary-btn {
        background: #fff;
        border-color: rgb(209 213 219 / 1);
        color: rgb(31 41 55 / 1);
    }

    .dark .ir-daily-fares-page .ir-fares-secondary-btn {
        background: rgb(31 41 55 / 1);
        border-color: rgb(255 255 255 / 0.18);
        color: rgb(229 231 235 / 1);
    }

    .ir-daily-fares-page .ir-fares-table-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .ir-daily-fares-page .ir-fares-table-title {
        margin: 0;
        font-size: 0.875rem;
        font-weight: 700;
        color: rgb(17 24 39 / 1);
    }

    .dark .ir-daily-fares-page .ir-fares-table-title {
        color: rgb(243 244 246 / 1);
    }

    .ir-daily-fares-page .ir-fares-table-wrap {
        border: 1px solid rgb(229 231 235 / 1);
        border-radius: 0.5rem;
        overflow: auto;
        max-height: 520px;
    }

    .dark .ir-daily-fares-page .ir-fares-table-wrap {
        border-color: rgb(255 255 255 / 0.12);
    }

    .ir-daily-fares-page .ir-fares-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 420px;
    }

    .ir-daily-fares-page .ir-fares-table thead th {
        background: rgb(249 250 251 / 1);
        border-bottom: 1px solid rgb(229 231 235 / 1);
        color: rgb(75 85 99 / 1);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding: 0.58rem 0.65rem;
        text-align: left;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .dark .ir-daily-fares-page .ir-fares-table thead th {
        background: rgb(17 24 39 / 1);
        border-bottom-color: rgb(255 255 255 / 0.12);
    }

    .ir-daily-fares-page .ir-fares-table tbody td {
        border-bottom: 1px solid rgb(229 231 235 / 1);
        padding: 0.58rem 0.65rem;
        font-size: 0.82rem;
        color: rgb(17 24 39 / 1);
        vertical-align: middle;
    }

    .dark .ir-daily-fares-page .ir-fares-table tbody td {
        border-bottom-color: rgb(255 255 255 / 0.08);
        color: rgb(229 231 235 / 1);
    }

    .ir-daily-fares-page .ir-fares-table .w-check {
        width: 36px;
    }

    .ir-daily-fares-page .ir-fares-table .text-right {
        text-align: right;
    }

    .ir-daily-fares-page .ir-fares-table .text-center {
        text-align: center;
        color: rgb(107 114 128 / 1);
    }

    .ir-daily-fares-page .ir-fares-pagination {
        margin-top: 0.65rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: rgb(75 85 99 / 1);
    }

    .ir-daily-fares-page .ir-fares-pagination-actions {
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .ir-daily-fares-page .ir-fares-pagination-actions button {
        border: 1px solid rgb(209 213 219 / 1);
        border-radius: 0.375rem;
        background: #fff;
        color: rgb(31 41 55 / 1);
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.25rem 0.45rem;
        cursor: pointer;
    }

    .ir-daily-fares-page .ir-fares-pagination-actions button[disabled] {
        opacity: 0.4;
        cursor: not-allowed;
    }
</style>

{{-- Default sidebar closed when no Alpine persist values yet. Left-edge hover opens via Alpine store; leaving strip/sidebar closes unless opened from the menu or after a click inside the sidebar. --}}
<script>
    (function () {
        document.addEventListener('alpine:init', function () {
            queueMicrotask(function () {
                var store = window.Alpine && window.Alpine.store('sidebar');
                if (!store) {
                    return;
                }

                if (
                    localStorage.getItem('isOpen') === null &&
                    localStorage.getItem('isOpenDesktop') === null
                ) {
                    store.close();
                }

                var openedBy = null;
                var rafHoverCheck = null;

                function sidebarEl() {
                    return document.querySelector('.fi-main-sidebar.fi-sidebar');
                }

                var openToggleSelector =
                    '.fi-topbar-open-sidebar-btn, .fi-topbar-open-collapse-sidebar-btn, .fi-sidebar-open-collapse-sidebar-btn, .fi-layout-sidebar-toggle-btn';
                var closeToggleSelector =
                    '.fi-topbar-close-sidebar-btn, .fi-topbar-close-collapse-sidebar-btn, .fi-sidebar-close-collapse-sidebar-btn, .fi-sidebar-close-overlay';

                document.body.addEventListener(
                    'click',
                    function (e) {
                        var t = e.target;
                        if (t.closest(openToggleSelector)) {
                            openedBy = 'menu';
                        }
                        if (t.closest(closeToggleSelector)) {
                            openedBy = null;
                        }

                        var aside = sidebarEl();
                        if (
                            aside &&
                            aside.contains(t) &&
                            openedBy === 'hover'
                        ) {
                            openedBy = 'menu';
                        }
                    },
                    true,
                );

                var strip = document.createElement('div');
                strip.className = 'fi-admin-sidebar-hover-strip';
                strip.setAttribute('aria-hidden', 'true');
                document.body.appendChild(strip);

                function updateStripVisibility() {
                    var lg = window.matchMedia('(min-width: 1024px)').matches;
                    strip.style.display = lg && !store.isOpen ? 'block' : 'none';
                }

                function scheduleHoverPointerCheck(e) {
                    if (openedBy !== 'hover' || !store.isOpen) {
                        return;
                    }
                    if (rafHoverCheck !== null) {
                        return;
                    }
                    rafHoverCheck = requestAnimationFrame(function () {
                        rafHoverCheck = null;
                        if (openedBy !== 'hover' || !store.isOpen) {
                            return;
                        }
                        var aside = sidebarEl();
                        if (!aside) {
                            return;
                        }
                        var el = document.elementFromPoint(e.clientX, e.clientY);
                        if (el && (aside.contains(el) || strip.contains(el))) {
                            return;
                        }
                        store.close();
                        openedBy = null;
                        updateStripVisibility();
                    });
                }

                document.addEventListener('pointermove', scheduleHoverPointerCheck, {
                    passive: true,
                });

                strip.addEventListener('mouseenter', function () {
                    if (!window.matchMedia('(min-width: 1024px)').matches) {
                        return;
                    }
                    if (!store.isOpen) {
                        openedBy = 'hover';
                        store.open();
                    }
                    updateStripVisibility();
                });

                document.addEventListener('livewire:navigated', function () {
                    updateStripVisibility();
                });

                updateStripVisibility();
                setInterval(updateStripVisibility, 400);
            });
        });
    })();
</script>

@include('filament.scripts.quick-save-shortcut')
