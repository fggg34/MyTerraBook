@php
    use Filament\Widgets\View\Components\ChartWidgetComponent;
    use Illuminate\View\ComponentAttributeBag;

    $pollingInterval = $this->getPollingInterval();
@endphp

<x-filament-widgets::widget>
    <div class="tb-welcome-header">
        <div class="tb-welcome-header-top">
            <div class="tb-welcome-header-intro">
                <p class="tb-welcome-header-greeting">{{ $greeting }}, {{ $name }}</p>
                <h2 class="tb-welcome-header-title">Welcome to your dashboard</h2>
            </div>
            <div class="tb-welcome-header-date">{{ $date }}</div>
        </div>

        <div class="tb-welcome-header-kpis">
            @foreach ($metrics as $metric)
                <div @class(['tb-welcome-header-kpi', 'fi-color', "fi-color-{$metric['color']}"])>
                    <span class="tb-welcome-header-kpi-value">{{ $metric['value'] }}</span>
                    <span class="tb-welcome-header-kpi-label">{{ $metric['label'] }}</span>
                    <span class="tb-welcome-header-kpi-description">{{ $metric['description'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="tb-welcome-header-chart-panel">
            <div class="tb-welcome-header-chart-heading">
                <h3 class="tb-welcome-header-chart-title">30-day operations overview</h3>
                <p class="tb-welcome-header-chart-subtitle">Daily active units and revenue trend</p>
            </div>

            <div
                class="tb-welcome-header-chart"
                @if ($pollingInterval)
                    wire:poll.{{ $pollingInterval }}="refreshDashboard"
                @endif
            >
                <div
                    wire:ignore
                    x-load
                    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                    data-chart-type="line"
                    x-data="chart({
                        cachedData: @js($chartData),
                        maxHeight: @js('300px'),
                        options: @js($chartOptions),
                        type: @js('line'),
                    })"
                    {{
                        (new ComponentAttributeBag)
                            ->color(ChartWidgetComponent::class, 'primary')
                            ->class([
                                'fi-wi-chart-canvas-ctn',
                                'fi-wi-chart-canvas-ctn-no-aspect-ratio',
                                'tb-welcome-header-chart-canvas',
                            ])
                    }}
                >
                    <canvas
                        x-ref="canvas"
                        style="max-height: 300px"
                    ></canvas>

                    <span
                        x-ref="backgroundColorElement"
                        class="fi-wi-chart-bg-color"
                    ></span>

                    <span
                        x-ref="borderColorElement"
                        class="fi-wi-chart-border-color"
                    ></span>

                    <span
                        x-ref="gridColorElement"
                        class="fi-wi-chart-grid-color"
                    ></span>

                    <span
                        x-ref="textColorElement"
                        class="fi-wi-chart-text-color"
                    ></span>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tb-welcome-header {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            padding: 1.75rem 2rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(var(--primary-500), 0.18);
            border-left: 4px solid rgb(var(--primary-500));
            background: linear-gradient(135deg, rgba(var(--primary-50), 0.9) 0%, #fff 55%);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }

        .tb-welcome-header-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1.5rem;
        }

        .tb-welcome-header-greeting {
            margin: 0 0 0.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(var(--primary-600));
            letter-spacing: 0.01em;
        }

        .tb-welcome-header-title {
            margin: 0 0 0.5rem;
            font-size: 1.625rem;
            line-height: 1.2;
            font-weight: 700;
            color: rgb(15 23 42);
        }

        .tb-welcome-header-date {
            flex-shrink: 0;
            padding: 0.5rem 0.875rem;
            border-radius: 9999px;
            background: #fff;
            border: 1px solid rgba(var(--primary-500), 0.15);
            font-size: 0.8125rem;
            font-weight: 600;
            color: rgb(71 85 105);
            white-space: nowrap;
        }

        .tb-welcome-header-kpis {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .tb-welcome-header-kpi {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 1.125rem 1.25rem;
            border-radius: 0.75rem;
            background: #fff;
            border: 1px solid rgba(var(--primary-500), 0.12);
            border-top: 3px solid rgb(var(--color-500));
        }

        .tb-welcome-header-kpi-value {
            font-size: 1.75rem;
            line-height: 1.1;
            font-weight: 700;
            color: rgb(15 23 42);
            font-variant-numeric: tabular-nums;
        }

        .tb-welcome-header-kpi-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(51 65 85);
        }

        .tb-welcome-header-kpi-description {
            font-size: 0.75rem;
            font-weight: 500;
            color: rgb(100 116 139);
        }

        .tb-welcome-header-chart-panel {
            padding: 1.25rem 1.5rem 1rem;
            border-radius: 0.75rem;
            background: #fff;
            border: 1px solid rgba(var(--primary-500), 0.12);
        }

        .tb-welcome-header-chart-heading {
            margin-bottom: 1rem;
        }

        .tb-welcome-header-chart-title {
            margin: 0 0 0.25rem;
            font-size: 1rem;
            font-weight: 700;
            color: rgb(15 23 42);
        }

        .tb-welcome-header-chart-subtitle {
            margin: 0;
            font-size: 0.8125rem;
            color: rgb(100 116 139);
        }

        .tb-welcome-header-chart {
            min-height: 300px;
        }

        .tb-welcome-header-chart-canvas {
            width: 100%;
            min-height: 300px;
        }

        .tb-welcome-header-chart-canvas canvas {
            width: 100% !important;
            height: 300px !important;
        }

        .dark .tb-welcome-header {
            background: linear-gradient(135deg, rgba(var(--primary-950), 0.45) 0%, rgb(15 23 42) 55%);
            border-color: rgba(var(--primary-400), 0.22);
        }

        .dark .tb-welcome-header-title {
            color: rgb(248 250 252);
        }

        .dark .tb-welcome-header-date,
        .dark .tb-welcome-header-kpi,
        .dark .tb-welcome-header-chart-panel {
            background: rgb(30 41 59);
            border-color: rgba(var(--primary-400), 0.18);
        }

        .dark .tb-welcome-header-kpi-value,
        .dark .tb-welcome-header-chart-title {
            color: rgb(248 250 252);
        }

        .dark .tb-welcome-header-kpi-label {
            color: rgb(226 232 240);
        }

        .dark .tb-welcome-header-kpi-description,
        .dark .tb-welcome-header-chart-subtitle {
            color: rgb(148 163 184);
        }

        @media (max-width: 768px) {
            .tb-welcome-header {
                padding: 1.25rem;
            }

            .tb-welcome-header-top {
                flex-direction: column;
            }

            .tb-welcome-header-kpis {
                grid-template-columns: 1fr;
            }

            .tb-welcome-header-chart-panel {
                padding: 1rem;
            }
        }
    </style>
</x-filament-widgets::widget>
