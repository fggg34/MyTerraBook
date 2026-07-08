<x-filament-panels::page>
    <style>
        .fi-main-ctn:has(.admin-calendar-inline) .fi-page-header-main-ctn,
        .fi-main-ctn:has(.admin-calendar-embed__frame) .fi-page-header-main-ctn {
            display: none !important;
        }

        .fi-main-ctn:has(.admin-calendar-inline) .fi-page-main,
        .fi-main-ctn:has(.admin-calendar-embed__frame) .fi-page-main {
            gap: 0 !important;
        }

        .fi-main-ctn:has(.admin-calendar-inline) .fi-page-content,
        .fi-main-ctn:has(.admin-calendar-embed__frame) .fi-page-content {
            padding: 0 !important;
        }

        .admin-calendar-inline {
            display: block;
            width: 100%;
            min-height: calc(100vh - 11.5rem);
            background: #fff;
        }

        .admin-calendar-inline #terrabook-calendar-root {
            min-height: inherit;
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
            .admin-calendar-inline {
                min-height: calc(100vh - 13rem);
            }

            .admin-calendar-embed__frame {
                height: calc(100vh - 13rem);
                min-height: 520px;
            }
        }
    </style>

    @php
        $embedJsUrl = $this->embedJsUrl ?? null;
        $embedCssUrl = $this->embedCssUrl ?? null;
        $handoffToken = $this->handoffToken ?? null;
        $embedUrl = $this->calendarEmbedUrl ?? '';
        $useInline = filled($embedJsUrl);
    @endphp

    @if ($useInline)
        <div class="admin-calendar-inline">
            @if (filled($handoffToken))
                <script>
                    window.__TERRABOOK_CALENDAR_HANDOFF__ = @json($handoffToken);
                </script>
            @endif
            @if (filled($embedCssUrl))
                <link rel="stylesheet" href="{{ $embedCssUrl }}" />
            @endif
            <div id="terrabook-calendar-root"></div>
            <script type="module" src="{{ $embedJsUrl }}"></script>
        </div>
        @if (config('app.debug'))
            <p style="margin:0.5rem 0 0;font-size:11px;color:#64748b;">
                Inline embed JS: <code>{{ $embedJsUrl }}</code>
            </p>
        @endif
    @elseif (blank($embedUrl))
        <div class="admin-calendar-embed__error">
            Calendar assets could not be loaded. Run <code>npm run build</code> in the frontend,
            upload <code>dist/</code> to the site root, and set
            <code>FRONTEND_URL=https://myterrabook.com</code> in <code>backend/.env</code>.
        </div>
    @else
        <iframe
            class="admin-calendar-embed__frame"
            src="{{ $embedUrl }}"
            title="Reservations calendar"
            loading="eager"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
        @if (config('app.debug'))
            <p style="margin:0.5rem 0 0;font-size:11px;color:#64748b;">
                Iframe fallback URL: <code>{{ $embedUrl }}</code>
            </p>
        @endif
    @endif
</x-filament-panels::page>
