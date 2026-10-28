<x-filament-panels::page>
    <div class="tb-report">
        <div class="tb-report-bar">
            <div class="tb-seg" role="tablist" aria-label="Statistics">
                <button type="button" @class(['is-active' => $this->activeTab === 'visitors']) wire:click="$set('activeTab', 'visitors')">Visitors</button>
                <button type="button" @class(['is-active' => $this->activeTab === 'conversion_rates']) wire:click="$set('activeTab', 'conversion_rates')">Conversion rates</button>
            </div>
            <div class="tb-seg" role="tablist" aria-label="Period">
                <button type="button" @class(['is-active' => $this->trackingDates === 'last_7_days']) wire:click="$set('trackingDates', 'last_7_days')">7 days</button>
                <button type="button" @class(['is-active' => $this->trackingDates === 'last_30_days']) wire:click="$set('trackingDates', 'last_30_days')">30 days</button>
                <button type="button" @class(['is-active' => $this->trackingDates === 'last_90_days']) wire:click="$set('trackingDates', 'last_90_days')">90 days</button>
                <button type="button" @class(['is-active' => $this->trackingDates === 'custom']) wire:click="$set('trackingDates', 'custom')">Custom</button>
            </div>
            <select wire:model.live="country" aria-label="Country">
                <option value="">All countries</option>
                @foreach ($this->countryOptions as $countryOption)
                    <option value="{{ $countryOption }}">{{ $countryOption }}</option>
                @endforeach
            </select>
            <select wire:model.live="referrer" aria-label="Referrer">
                <option value="">All referrers</option>
                @foreach ($this->referrerOptions as $referrerOption)
                    <option value="{{ $referrerOption }}">{{ $referrerOption }}</option>
                @endforeach
            </select>
            <input type="search" wire:model.live.debounce.300ms="query" placeholder="Search…" aria-label="Search results">
            <button type="button" class="tb-mini-btn" wire:click="clearFilters">Clear</button>
        </div>
        @if ($this->trackingDates === 'custom')
            <div class="tb-report-dates">
                <input type="date" wire:model.live="from" aria-label="From">
                <input type="date" wire:model.live="to" aria-label="To">
            </div>
        @endif

        @if ($this->activeTab === 'visitors')
            <div class="tb-stats">
                <div class="tb-stat"><span>Total visitors</span><strong>{{ (int) ($this->averageValues['total_visitors'] ?? 0) }}</strong></div>
                <div class="tb-stat"><span>Total bookings</span><strong>{{ (int) ($this->averageValues['total_bookings'] ?? 0) }}</strong></div>
                <div class="tb-stat"><span>Average rental length</span><strong>{{ number_format((float) ($this->averageValues['average_length_of_rent'] ?? 0), 1) }} days</strong></div>
                <div class="tb-stat"><span>Conversion rate</span><strong>{{ number_format((float) ($this->averageValues['average_conversion_rate'] ?? 0), 2) }}%</strong></div>
            </div>
            <div class="tb-report-split">
                <section class="tb-table-card">
                    <header style="padding:14px 16px"><strong>Most demanded days</strong></header>
                    @forelse ($this->mostDemandedDays as $day)
                        <div class="tb-row tb-grid" style="grid-template-columns:minmax(140px,1fr) 140px 140px">
                            <span>{{ \Carbon\Carbon::parse($day['date'])->format('D, j M Y') }}</span>
                            <span>{{ $day['requests'] }} {{ $day['requests'] === 1 ? 'request' : 'requests' }}</span>
                            <span>{{ $day['visitors'] }} {{ $day['visitors'] === 1 ? 'visitor' : 'visitors' }}</span>
                        </div>
                    @empty
                        <p class="tb-empty">No tracking events for this period.</p>
                    @endforelse
                </section>
                <section class="tb-table-card">
                    <header style="padding:14px 16px"><strong>Best referrers</strong></header>
                    @forelse ($this->bestReferrers as $referrerRow)
                        <div class="tb-row tb-grid" style="grid-template-columns:minmax(0,1fr) auto">
                            <span>{{ $referrerRow['referrer'] }}</span>
                            <span>{{ $referrerRow['visitors'] }} {{ $referrerRow['visitors'] === 1 ? 'visitor' : 'visitors' }}</span>
                        </div>
                    @empty
                        <p class="tb-empty">No referrer data.</p>
                    @endforelse
                </section>
            </div>
        @else
            <section class="tb-table-card">
                <header style="padding:14px 16px"><strong>Daily conversion rates</strong></header>
                @if ($this->conversionRates === [])
                    <p class="tb-empty">No conversion-rate data for these filters.</p>
                @else
                    <div class="tb-head tb-grid" style="grid-template-columns:160px 120px 120px 140px"><span>Date</span><span>Visitors</span><span>Bookings</span><span>Conversion rate</span></div>
                    @foreach ($this->conversionRates as $row)
                        <div class="tb-row tb-grid" style="grid-template-columns:160px 120px 120px 140px">
                            <span>{{ \Carbon\Carbon::parse($row['date'])->format('D, j M Y') }}</span>
                            <span>{{ $row['visitors'] }}</span>
                            <span>{{ $row['bookings'] }}</span>
                            <span>{{ number_format($row['conversion_rate'], 2) }}%</span>
                        </div>
                    @endforeach
                @endif
            </section>
        @endif
    </div>
</x-filament-panels::page>
