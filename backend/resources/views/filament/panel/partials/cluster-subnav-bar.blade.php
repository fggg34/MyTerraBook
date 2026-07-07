@php
    $dataPrefix = $dataPrefix ?? 'cluster';
    $triggerAttr = 'data-'.$dataPrefix.'-trigger';
    $quickAccessAttr = 'data-'.$dataPrefix.'-quick-access';
@endphp

<style>
    .cluster-subnav-outer {
        display: flex;
        justify-content: center;
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        scrollbar-width: none;
    }

    .cluster-subnav-outer::-webkit-scrollbar { display: none; }

    .cluster-subnav {
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

    .cluster-subnav__entry {
        position: relative;
        display: flex;
    }

    .cluster-subnav__item {
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

    .cluster-subnav__item:hover {
        background-color: rgb(249 250 251);
        color: rgb(55 65 81);
    }

    .cluster-subnav__item:focus-visible {
        background-color: rgb(249 250 251);
        outline: none;
    }

    .cluster-subnav__item--active {
        background-color: rgb(249 250 251);
        color: var(--mtb-primary-dark);
    }

    .cluster-subnav__icon {
        width: 1rem;
        height: 1rem;
        flex-shrink: 0;
        stroke: currentColor;
        fill: none;
        color: rgb(156 163 175);
    }

    .cluster-subnav__item--active .cluster-subnav__icon {
        color: var(--mtb-primary-dark);
    }

    .cluster-subnav__chevron {
        width: 0.75rem;
        height: 0.75rem;
        color: rgb(156 163 175);
        transition: transform 150ms ease;
        flex-shrink: 0;
    }

    .cluster-subnav__entry[data-open] .cluster-subnav__chevron {
        transform: rotate(180deg);
    }

    .cluster-subnav__dropdown {
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

    .cluster-subnav__entry[data-open] .cluster-subnav__dropdown {
        display: block;
    }

    .cluster-subnav__dropdown-item {
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

    .cluster-subnav__dropdown-item:hover {
        background-color: rgb(249 250 251);
        color: rgb(55 65 81);
    }

    .cluster-subnav__dropdown-item--active {
        background-color: rgb(249 250 251);
        color: var(--mtb-primary-dark);
        font-weight: 500;
    }

    .cluster-subnav-mobile {
        display: none;
        width: 100%;
        padding: 0 0.25rem;
    }

    .cluster-subnav-mobile__label {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: rgb(107 114 128);
    }

    .cluster-subnav-mobile__select {
        width: 100%;
        border: 1px solid var(--mtb-line);
        border-radius: 0.65rem;
        background: #fff;
        color: rgb(31 41 55);
        font-size: 0.9375rem;
        font-weight: 500;
        padding: 0.55rem 2rem 0.55rem 0.75rem;
        min-height: 44px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.65rem center;
        background-size: 1.1rem;
    }

    @media (max-width: 768px) {
        .cluster-subnav-outer {
            display: none;
        }

        .cluster-subnav-mobile {
            display: block;
        }
    }
</style>

<div class="cluster-subnav-mobile" {{ $quickAccessAttr }}>
    <label class="cluster-subnav-mobile__label" for="{{ $dataPrefix }}-tabs-mobile-select">{{ $mobileLabel }}</label>
    <select
        id="{{ $dataPrefix }}-tabs-mobile-select"
        class="cluster-subnav-mobile__select"
        aria-label="{{ $ariaLabel }}"
        onchange="if (this.value) window.location.href = this.value"
    >
        @foreach ($mobileNavOptions ?? [] as $option)
            <option value="{{ $option['url'] }}" @selected($option['url'] === ($activeMobileUrl ?? ''))>
                {{ $option['label'] }}
            </option>
        @endforeach
    </select>
</div>

<div class="cluster-subnav-outer" {{ $quickAccessAttr }}>
    <nav class="cluster-subnav" aria-label="{{ $ariaLabel }}">
        @foreach ($quickLinks as $link)
            <div class="cluster-subnav__entry">
                @if (!empty($link['items']))
                    <button
                        type="button"
                        class="cluster-subnav__item {{ $link['active'] ? 'cluster-subnav__item--active' : '' }}"
                        aria-haspopup="true"
                        aria-expanded="false"
                        {{ $triggerAttr }}
                    >
                        <svg class="cluster-subnav__icon" viewBox="0 0 24 24" aria-hidden="true">
                            {!! $link['icon'] !!}
                        </svg>
                        <span>{{ $link['label'] }}</span>
                        <svg class="cluster-subnav__chevron" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div class="cluster-subnav__dropdown" role="menu">
                        @foreach ($link['items'] as $sub)
                            <a
                                href="{{ $sub['url'] }}"
                                role="menuitem"
                                class="cluster-subnav__dropdown-item {{ ($sub['active'] ?? false) ? 'cluster-subnav__dropdown-item--active' : '' }}"
                                @if (($sub['active'] ?? false)) aria-current="page" @endif
                            >
                                {{ $sub['label'] }}
                            </a>
                        @endforeach
                    </div>
                @else
                    <a
                        href="{{ $link['url'] }}"
                        class="cluster-subnav__item {{ $link['active'] ? 'cluster-subnav__item--active' : '' }}"
                        @if ($link['active']) aria-current="page" @endif
                    >
                        <svg class="cluster-subnav__icon" viewBox="0 0 24 24" aria-hidden="true">
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
@once
(function () {
    const triggerSelector = '[data-cluster-trigger], [data-ir-trigger], [data-gh-trigger]';

    function positionDropdown(trigger, dropdown) {
        const rect = trigger.getBoundingClientRect();
        dropdown.style.top = (rect.bottom + 6) + 'px';
        dropdown.style.left = (rect.left + rect.width / 2) + 'px';
        dropdown.style.transform = 'translateX(-50%)';

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
        document.querySelectorAll('.cluster-subnav__entry[data-open]').forEach((el) => {
            if (el !== exceptEntry) {
                el.removeAttribute('data-open');
                const btn = el.querySelector(triggerSelector);
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    document.addEventListener('click', function (e) {
        const trigger = e.target.closest(triggerSelector);
        if (trigger) {
            e.preventDefault();
            e.stopPropagation();

            const entry = trigger.closest('.cluster-subnav__entry');
            if (!entry) return;

            const dropdown = entry.querySelector('.cluster-subnav__dropdown');
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

        if (e.target.closest('.cluster-subnav__dropdown')) return;
        closeAll(null);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAll(null);
    });

    function repositionOpen() {
        document.querySelectorAll('.cluster-subnav__entry[data-open]').forEach((entry) => {
            const trigger = entry.querySelector(triggerSelector);
            const dropdown = entry.querySelector('.cluster-subnav__dropdown');
            if (trigger && dropdown) positionDropdown(trigger, dropdown);
        });
    }

    window.addEventListener('resize', repositionOpen);
    window.addEventListener('scroll', repositionOpen, true);
})();
@endonce
</script>
