<x-filament-widgets::widget>
    <div class="tb-welcome-header">
        <div class="tb-welcome-header-intro">
            <p class="tb-welcome-header-date">{{ $date }}</p>
            <h2 class="tb-welcome-header-title">{{ $greeting }}, {{ $name }}</h2>
        </div>
        <div class="tb-welcome-actions">
            <a class="tb-btn" href="{{ $reportsUrl }}">
                <img src="{{ asset('admin-redesign/icon-btn-period.svg') }}" alt="" width="16" height="16">
                Last 30 days
            </a>
            <a class="tb-btn tb-btn-steel" href="{{ $calendarUrl }}">
                <img src="{{ asset('admin-redesign/icon-btn-calendar.svg') }}" alt="" width="16" height="16">
                Open calendar
            </a>
        </div>
    </div>

    <style>
        .tb-welcome-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            padding: 0;
            background: transparent;
            border: 0;
            box-shadow: none;
        }
        .tb-welcome-header-date {
            margin: 0 0 4px;
            font-family: "IBM Plex Sans", sans-serif;
            font-size: 12px;
            font-weight: 400;
            line-height: 16px;
            letter-spacing: 0;
            text-transform: none;
            color: #8a99a9;
        }
        .tb-welcome-header-title {
            margin: 0;
            font-family: Manrope, sans-serif;
            font-size: 28px;
            font-weight: 800;
            line-height: 36px;
            letter-spacing: -0.5px;
            color: #041e37;
        }
        .tb-welcome-actions { display: flex; gap: 8px; }
        .tb-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 16px;
            border: 1px solid #dbe3ec;
            border-radius: 6px;
            background: #fff;
            color: #0d1b2a;
            font-family: "IBM Plex Sans", sans-serif;
            font-size: 14px;
            font-weight: 600;
            line-height: 20px;
            text-decoration: none;
        }
        .tb-btn img { width: 16px; height: 16px; }
        .tb-btn-steel { border-color: #4474a4; color: #4474a4; }

        .dark .tb-welcome-header {
            background: rgb(30 41 59);
            border-color: rgb(51 65 85);
        }

        .dark .tb-welcome-header-title {
            color: rgb(248 250 252);
        }

        .dark .tb-welcome-header-date {
            background: rgb(15 23 42);
            border-color: rgb(51 65 85);
        }

        @media (max-width: 768px) {
            .tb-welcome-header {
                flex-direction: column;
                padding: 1.25rem;
                gap: 1rem;
            }

            .tb-welcome-header-title {
                font-size: 1.25rem;
            }

            .tb-welcome-header-date {
                align-self: flex-start;
                white-space: normal;
                text-align: left;
                line-height: 1.4;
            }
        }
    </style>
</x-filament-widgets::widget>
