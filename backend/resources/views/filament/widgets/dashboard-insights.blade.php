@php
    $pollingInterval = $this->getPollingInterval();
@endphp

<x-filament-widgets::widget
    :attributes="
        (new \Illuminate\View\ComponentAttributeBag)
            ->merge([
                'wire:poll.' . $pollingInterval => $pollingInterval ? true : null,
            ], escape: false)
    "
>
    <div class="tb-insights">
        <section class="tb-insights-section">
            <h3 class="tb-insights-heading">Top countries (30 days)</h3>
            @if (count($topCountries) > 0)
                <ul class="tb-insights-countries">
                    @foreach ($topCountries as $country)
                        <li class="tb-insights-country">
                            <span class="tb-insights-country-name">{{ $country['country'] }}</span>
                            <span class="tb-insights-country-meta">
                                {{ $country['confirmed_orders'] }} orders · €{{ $country['revenue'] }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="tb-insights-empty">No country data for the last 30 days yet.</p>
            @endif
        </section>
    </div>

    <style>
        .tb-insights {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            height: 100%;
        }

        .tb-insights-section {
            padding: 1rem 1.125rem;
            border-radius: 0.75rem;
            border: 1px solid rgb(226 232 240);
            background: #fff;
        }

        .dark .tb-insights-section {
            border-color: rgb(51 65 85);
            background: rgb(30 41 59);
        }

        .tb-insights-heading {
            margin: 0 0 0.875rem;
            font-size: 0.9375rem;
            font-weight: 700;
            color: rgb(15 23 42);
        }

        .dark .tb-insights-heading {
            color: rgb(248 250 252);
        }

        .tb-insights-countries {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
        }

        .tb-insights-country {
            display: flex;
            flex-direction: column;
            gap: 0.125rem;
            padding-bottom: 0.625rem;
            border-bottom: 1px solid rgb(241 245 249);
        }

        .dark .tb-insights-country {
            border-bottom-color: rgb(51 65 85);
        }

        .tb-insights-country:last-child {
            padding-bottom: 0;
            border-bottom: none;
        }

        .tb-insights-country-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(30 41 59);
        }

        .dark .tb-insights-country-name {
            color: rgb(226 232 240);
        }

        .tb-insights-country-meta {
            font-size: 0.75rem;
            color: rgb(100 116 139);
        }

        .dark .tb-insights-country-meta {
            color: rgb(148 163 184);
        }

        .tb-insights-empty {
            margin: 0;
            font-size: 0.8125rem;
            color: rgb(100 116 139);
        }

        .dark .tb-insights-empty {
            color: rgb(148 163 184);
        }
    </style>
</x-filament-widgets::widget>
