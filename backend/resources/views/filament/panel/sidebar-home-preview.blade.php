@php
    $frontendUrl = rtrim((string) config('app.frontend_url', config('app.url')), '/');
@endphp

<style>
    .fi-sidebar-item-home {
        position: relative;
    }

    .fi-sidebar-item-home > .fi-sidebar-item-btn {
        padding-inline-end: 2.25rem;
    }

    .fi-sidebar-home-preview {
        position: absolute;
        inset-block: 0;
        inset-inline-end: 0.35rem;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.75rem;
        color: rgb(107 114 128);
        opacity: 0;
        transition: opacity 120ms ease, color 120ms ease;
    }

    .fi-sidebar-item-home:hover .fi-sidebar-home-preview,
    .fi-sidebar-home-preview:focus-visible {
        opacity: 1;
    }

    .fi-sidebar-home-preview:hover {
        color: var(--mtb-green, #45a06a);
    }

    .fi-sidebar-home-preview svg {
        width: 1.125rem;
        height: 1.125rem;
        stroke: currentColor;
        fill: none;
    }

    .fi-body-has-sidebar-collapsible-on-desktop .fi-sidebar:not(.fi-sidebar-open) .fi-sidebar-home-preview {
        display: none;
    }
</style>

<script>
    (function () {
        var previewUrl = @json($frontendUrl);

        function enhanceHomeNav() {
            document.querySelectorAll('.fi-sidebar-item-home').forEach(function (item) {
                if (item.querySelector('.fi-sidebar-home-preview')) {
                    return;
                }

                var preview = document.createElement('a');
                preview.href = previewUrl;
                preview.target = '_blank';
                preview.rel = 'noopener noreferrer';
                preview.className = 'fi-sidebar-home-preview';
                preview.setAttribute('aria-label', 'Preview website');
                preview.innerHTML =
                    '<svg viewBox="0 0 24 24" aria-hidden="true">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"></path>' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"></path>' +
                    '</svg>';

                preview.addEventListener('click', function (event) {
                    event.stopPropagation();
                });

                item.appendChild(preview);
            });
        }

        document.addEventListener('DOMContentLoaded', enhanceHomeNav);
        document.addEventListener('livewire:navigated', enhanceHomeNav);
    })();
</script>
