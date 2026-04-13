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
