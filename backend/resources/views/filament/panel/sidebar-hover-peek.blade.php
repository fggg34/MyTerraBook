{{-- Hover strip + JS: Filament hides sidebar content when isOpen is false, so peek must use the sidebar store. --}}
<style>
    .fi-admin-sidebar-hover-strip {
        position: fixed;
        top: 4rem;
        left: 0;
        width: 16px;
        height: calc(100dvh - 4rem);
        z-index: 35;
        display: none;
        pointer-events: auto;
    }

    html[dir='rtl'] .fi-admin-sidebar-hover-strip {
        left: auto;
        right: 0;
    }

    @media (max-width: 1023px) {
        .fi-admin-sidebar-hover-strip {
            display: none !important;
            pointer-events: none !important;
        }
    }
</style>
