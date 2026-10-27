<x-filament-widgets::widget>
    <div class="tb-welcome-header">
        <div class="tb-welcome-header-intro">
            <p class="tb-welcome-header-date">{{ $date }}</p>
            <h2 class="tb-welcome-header-title">{{ $greeting }}, {{ $name }}</h2>
        </div>
    </div>

    <style>
        .tb-welcome-header {
            padding: 4px 0 8px;
            background: transparent;
            border: 0;
            box-shadow: none;
        }

        .tb-welcome-header-date {
            margin: 0 0 4px;
            font-family: "IBM Plex Mono", ui-monospace, monospace;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #8a99a9;
        }

        .tb-welcome-header-title {
            margin: 0;
            font-family: Manrope, sans-serif;
            font-size: 28px;
            font-weight: 800;
            line-height: 36px;
            letter-spacing: -0.5px;
            color: #0d1b2a;
        }

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
