@php
    use Filament\Widgets\View\Components\ChartWidgetComponent;
    use Illuminate\View\ComponentAttributeBag;

    $color = $this->getColor();
    $heading = $this->getHeading();
    $description = $this->getDescription();
    $type = $this->getType();
    $hasActivity = $this->hasChartActivity();
@endphp

<x-filament-widgets::widget class="fi-wi-chart">
    <x-filament::section
        :description="$description"
        :heading="$heading"
    >
        @if ($hasActivity)
            <div
                @if ($pollingInterval = $this->getPollingInterval())
                    wire:poll.{{ $pollingInterval }}="updateChartData"
                @endif
            >
                <div
                    x-load
                    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('chart', 'filament/widgets') }}"
                    wire:ignore
                    data-chart-type="{{ $type }}"
                    x-data="chart({
                        cachedData: @js($this->getCachedData()),
                        maxHeight: @js($maxHeight = $this->getMaxHeight()),
                        options: @js($this->getOptions()),
                        type: @js($type),
                    })"
                    {{
                        (new ComponentAttributeBag)
                            ->color(ChartWidgetComponent::class, $color)
                            ->class([
                                'fi-wi-chart-canvas-ctn',
                                'fi-wi-chart-canvas-ctn-no-aspect-ratio' => filled($maxHeight),
                            ])
                    }}
                >
                    <canvas
                        x-ref="canvas"
                        @if ($maxHeight)
                            style="max-height: {{ $maxHeight }}"
                        @endif
                    ></canvas>

                    <span x-ref="backgroundColorElement" class="fi-wi-chart-bg-color"></span>
                    <span x-ref="borderColorElement" class="fi-wi-chart-border-color"></span>
                    <span x-ref="gridColorElement" class="fi-wi-chart-grid-color"></span>
                    <span x-ref="textColorElement" class="fi-wi-chart-text-color"></span>
                </div>
            </div>
        @else
            <div class="tb-chart-empty">
                <div class="tb-chart-empty-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 14l4-4 3 3 5-6" />
                    </svg>
                </div>
                <p class="tb-chart-empty-title">No activity yet</p>
                <p class="tb-chart-empty-copy">
                    Confirmed bookings and revenue will appear here once you have orders or guest stays in the last 30 days.
                </p>
            </div>
        @endif
    </x-filament::section>

    <style>
        .tb-chart-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 280px;
            padding: 2rem 1.5rem;
            border: 1px dashed rgb(226 232 240);
            border-radius: 0.75rem;
            background: rgb(248 250 252);
            text-align: center;
        }

        .dark .tb-chart-empty {
            border-color: rgb(51 65 85);
            background: rgb(15 23 42 / 0.45);
        }

        .tb-chart-empty-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            margin-bottom: 0.25rem;
            border-radius: 9999px;
            background: rgb(255 255 255);
            color: rgb(100 116 139);
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.06);
        }

        .dark .tb-chart-empty-icon {
            background: rgb(30 41 59);
            color: rgb(148 163 184);
        }

        .tb-chart-empty-icon svg {
            width: 1.5rem;
            height: 1.5rem;
        }

        .tb-chart-empty-title {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: rgb(15 23 42);
        }

        .dark .tb-chart-empty-title {
            color: rgb(248 250 252);
        }

        .tb-chart-empty-copy {
            margin: 0;
            max-width: 28rem;
            font-size: 0.8125rem;
            line-height: 1.5;
            color: rgb(100 116 139);
        }

        .dark .tb-chart-empty-copy {
            color: rgb(148 163 184);
        }
    </style>
</x-filament-widgets::widget>
