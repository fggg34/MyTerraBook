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

        .admin-calendar-inline__mount {
            min-height: inherit;
        }

        .admin-calendar-inline__status {
            margin: 1rem;
            font-size: 0.875rem;
            color: #64748b;
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
            <div
                id="terrabook-calendar-root"
                class="admin-calendar-inline__mount"
                wire:ignore
                x-data
                x-init="
                    if (window.__terrabookCalendarBooted) return;
                    window.__terrabookCalendarBooted = true;
                    @if (filled($handoffToken))
                    window.__TERRABOOK_CALENDAR_HANDOFF__ = @js($handoffToken);
                    @endif
                    @if (filled($embedCssUrl))
                    if (! document.querySelector('[data-tb-calendar-css]')) {
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = @js($embedCssUrl);
                        link.setAttribute('data-tb-calendar-css', '1');
                        document.head.appendChild(link);
                    }
                    @endif
                    if (! document.querySelector('[data-tb-calendar-js]')) {
                        const script = document.createElement('script');
                        script.type = 'module';
                        script.src = @js($embedJsUrl);
                        script.setAttribute('data-tb-calendar-js', '1');
                        script.onerror = () => {
                            const root = document.getElementById('terrabook-calendar-root');
                            if (root) {
                                root.innerHTML = '<p class=&quot;admin-calendar-embed__error&quot;>Could not load calendar assets. Rebuild the frontend and upload dist/ to the site root.</p>';
                            }
                        };
                        document.head.appendChild(script);
                    }
                "
            >
                <p class="admin-calendar-inline__status">Loading calendar...</p>
            </div>
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
            <code>FRONTEND_URL=https://myterrabook.com</code> and
            <code>SPA_INDEX_PATH=/home/YOUR_USER/public_html/index.html</code> in <code>backend/.env</code>.
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
