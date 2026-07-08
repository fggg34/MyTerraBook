@php
    /** @var string $calendarEmbedUrl */
@endphp

<x-filament-panels::page>
    @push('styles')
        <style>
            .fi-main-ctn:has(.admin-calendar-embed__frame) .fi-page-header-main-ctn {
                display: none !important;
            }

            .fi-main-ctn:has(.admin-calendar-embed__frame) .fi-page-main {
                gap: 0 !important;
            }

            .fi-main-ctn:has(.admin-calendar-embed__frame) .fi-page-content {
                padding: 0 !important;
            }

            .admin-calendar-embed__frame {
                display: block;
                width: 100%;
                height: calc(100vh - 11.5rem);
                min-height: 640px;
                border: 0;
                border-radius: 0;
                background: #fff;
            }

            @media (max-width: 1024px) {
                .admin-calendar-embed__frame {
                    height: calc(100vh - 13rem);
                    min-height: 520px;
                }
            }
        </style>
    @endpush

    <iframe
        class="admin-calendar-embed__frame"
        src="{{ $calendarEmbedUrl }}"
        title="Reservations calendar"
        loading="lazy"
    ></iframe>
</x-filament-panels::page>
