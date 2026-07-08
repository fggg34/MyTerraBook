<x-filament-panels::page>
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

        .admin-calendar-embed__error {
            margin: 1rem;
            padding: 1rem 1.25rem;
            border: 1px solid #fecaca;
            border-radius: 0.75rem;
            background: #fef2f2;
            color: #991b1b;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        @media (max-width: 1024px) {
            .admin-calendar-embed__frame {
                height: calc(100vh - 13rem);
                min-height: 520px;
            }
        }
    </style>

    @php($embedUrl = $this->calendarEmbedUrl)

    @if (blank($embedUrl))
        <div class="admin-calendar-embed__error">
            Calendar embed URL is missing. Check <code>FRONTEND_URL</code> in <code>backend/.env</code>
            and ensure the React frontend is running.
        </div>
    @else
        <iframe
            class="admin-calendar-embed__frame"
            src="{{ $embedUrl }}"
            title="Reservations calendar"
            loading="eager"
        ></iframe>
    @endif
</x-filament-panels::page>
