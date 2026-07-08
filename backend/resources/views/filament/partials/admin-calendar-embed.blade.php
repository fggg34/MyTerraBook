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
        $embedMode = $useInline ? 'inline' : (blank($embedUrl) ? 'error' : 'iframe');
        // #region agent log
        \App\Support\CalendarEmbedDebug::log(
            'admin-calendar-embed.blade.php',
            'Blade render branch',
            ['embedMode' => $embedMode, 'embedJsUrl' => $embedJsUrl, 'embedUrl' => $embedUrl],
            'H1',
        );
        // #endregion
        $debugBeaconUrl = url('/admin/debug/calendar-embed-log');
    @endphp

    <div
        id="calendar-embed-debug-strip"
        data-mode="{{ $embedMode }}"
        data-js="{{ $embedJsUrl }}"
        data-iframe="{{ $embedUrl }}"
        style="margin:0.5rem 1rem;padding:0.5rem 0.75rem;font:12px/1.4 monospace;background:#f8fafc;border:1px solid #e2e8f0;border-radius:0.5rem;color:#334155;"
    >
        Calendar debug: mode={{ $embedMode }} | js={{ $embedJsUrl ?: 'none' }} | iframe={{ $embedUrl ?: 'none' }} | alpine=<span id="calendar-embed-alpine-status">pending</span> | react=<span id="calendar-embed-react-status">pending</span>
    </div>

    @if ($useInline)
        <div class="admin-calendar-inline">
            <div
                id="terrabook-calendar-root"
                class="admin-calendar-inline__mount"
                wire:ignore
                x-data
                x-init="
                    const __tbDbg = (msg, data, hid) => {
                        fetch(@js($debugBeaconUrl), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({ location: 'blade.x-init', message: msg, data, hypothesisId: hid }),
                        }).catch(() => {});
                        fetch('http://127.0.0.1:7876/ingest/51365707-604b-4c5c-b2ec-cfe2c3d9fec8', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-Debug-Session-Id': '66744b' },
                            body: JSON.stringify({ sessionId: '66744b', location: 'blade.x-init', message: msg, data, hypothesisId: hid, timestamp: Date.now() }),
                        }).catch(() => {});
                    };
                    __tbDbg('Alpine x-init started', { hasHandoff: @js(filled($handoffToken)), embedJsUrl: @js($embedJsUrl) }, 'H2');
                    const alpineStatus = document.getElementById('calendar-embed-alpine-status');
                    if (alpineStatus) alpineStatus.textContent = 'running';
                    if (window.__terrabookCalendarBooted) {
                        __tbDbg('Alpine skipped duplicate boot', {}, 'H2');
                        if (alpineStatus) alpineStatus.textContent = 'skipped-duplicate';
                        return;
                    }
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
                        script.onload = () => {
                            __tbDbg('Calendar module script loaded', { src: script.src }, 'H3');
                            if (alpineStatus) alpineStatus.textContent = 'script-loaded';
                        };
                        script.onerror = () => {
                            __tbDbg('Calendar module script failed', { src: script.src }, 'H3');
                            if (alpineStatus) alpineStatus.textContent = 'script-error';
                            const root = document.getElementById('terrabook-calendar-root');
                            if (root) {
                                root.innerHTML = '<p class=&quot;admin-calendar-embed__error&quot;>Could not load calendar assets. Rebuild the frontend and upload dist/ to the site root.</p>';
                            }
                        };
                        document.head.appendChild(script);
                        __tbDbg('Calendar module script injected', { src: script.src }, 'H3');
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
