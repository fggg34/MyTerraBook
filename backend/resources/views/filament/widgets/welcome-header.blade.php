<x-filament-widgets::widget>
    <div class="tb-welcome-header">
        <div class="tb-welcome-header-intro">
            <p class="tb-welcome-header-greeting">{{ $greeting }}, {{ $name }}</p>
            <h2 class="tb-welcome-header-title">Welcome to your dashboard</h2>
        </div>
        <div class="tb-welcome-header-date">{{ $date }}</div>
    </div>

    <style>
        .tb-welcome-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.5rem 1.75rem;
            border-radius: 0.75rem;
            border: 1px solid var(--mtb-line);
            border-left: 4px solid var(--mtb-primary);
            background: #fff;
            box-shadow: 0 1px 3px rgb(51 78 104 / 0.08);
        }

        .tb-welcome-header-greeting {
            margin: 0 0 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--mtb-primary-light);
            letter-spacing: 0.01em;
        }

        .tb-welcome-header-title {
            margin: 0;
            font-size: 1.5rem;
            line-height: 1.2;
            font-weight: 700;
            color: var(--mtb-primary-dark);
        }

        .tb-welcome-header-date {
            flex-shrink: 0;
            padding: 0.5rem 0.875rem;
            border-radius: 9999px;
            background: var(--mtb-bg);
            border: 1px solid var(--mtb-line);
            font-size: 0.8125rem;
            font-weight: 600;
            color: var(--mtb-muted);
            white-space: nowrap;
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
            }
        }
    </style>
</x-filament-widgets::widget>
