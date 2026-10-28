@php
    use App\Support\AdminMoney;
    use Carbon\Carbon;

    $bookedDays = array_sum(array_column($this->occupancyRanking, 'booked_days'));
    $orders = (int) ($this->revenueSummary['confirmed_orders'] ?? 0);
    $averageDays = $orders > 0 ? $bookedDays / $orders : 0;
    $fromLabel = Carbon::parse($this->from)->format('j M');
    $toLabel = Carbon::parse($this->to)->format('j M Y');
@endphp
<x-filament-panels::page>
    <div class="tb-report">
        <div class="tb-report-bar">
            <label>
                Report type
                <select wire:model.live="reportType">
                    <option value="occupancy_ranking">Occupancy ranking</option>
                    <option value="revenue">Revenue</option>
                    <option value="rate_plan_revenue">Rate plans revenue</option>
                    <option value="top_countries">Top countries</option>
                </select>
            </label>
            <div class="tb-seg" role="tablist" aria-label="Period">
                <button type="button" @class(['is-active' => $this->period === '7']) wire:click="setReportPeriod('7')">7 days</button>
                <button type="button" @class(['is-active' => $this->period === '30']) wire:click="setReportPeriod('30')">30 days</button>
                <button type="button" @class(['is-active' => $this->period === '90']) wire:click="setReportPeriod('90')">90 days</button>
                <button type="button" @class(['is-active' => $this->period === 'year']) wire:click="setReportPeriod('year')">This year</button>
                <button type="button" @class(['is-active' => $this->period === 'custom']) wire:click="$set('period', 'custom')">Custom</button>
            </div>
        </div>
        @if ($this->period === 'custom')
            <div class="tb-report-dates">
                <input type="date" wire:model.live="from" aria-label="From">
                <input type="date" wire:model.live="to" aria-label="To">
            </div>
        @endif
        <div class="tb-stats">
            <div class="tb-stat"><span>Booked days</span><strong>{{ number_format($bookedDays, 0, '.', '.') }}</strong></div>
            <div class="tb-stat"><span>Occupancy</span><strong>—</strong></div>
            <div class="tb-stat"><span>Revenue (confirmed)</span><strong>{{ AdminMoney::fromCents((int) ($this->revenueSummary['revenue_cents'] ?? 0)) }}</strong></div>
            <div class="tb-stat"><span>Average booked days</span><strong>{{ number_format($averageDays, 0, '.', '.') }} days</strong></div>
        </div>
        <section class="tb-table-card">
            @if ($this->reportType === 'revenue')
                <div class="tb-stats" style="padding:16px">
                    <div class="tb-stat"><span>Confirmed orders</span><strong>{{ $orders }}</strong></div>
                    <div class="tb-stat"><span>Revenue</span><strong>{{ AdminMoney::fromCents((int) ($this->revenueSummary['revenue_cents'] ?? 0)) }}</strong></div>
                    <div class="tb-stat"><span>Average order value</span><strong>{{ AdminMoney::fromCents((int) ($this->revenueSummary['average_order_value_cents'] ?? 0)) }}</strong></div>
                </div>
            @elseif ($this->reportType === 'occupancy_ranking')
                <header class="tb-email-group header" style="padding:14px 16px"><strong>Occupancy ranking · {{ $fromLabel }} – {{ $toLabel }}</strong></header>
                @if ($this->occupancyRanking === [])
                    <div class="tb-empty-card"><strong>No occupancy data for this period</strong><p>Change the period or report type. Data appears once bookings are confirmed.</p></div>
                @else
                    <div class="tb-head tb-grid" style="grid-template-columns:minmax(160px,1.4fr) 140px 120px 120px"><span>Vehicle</span><span>Confirmed orders</span><span>Booked hours</span><span>Booked days</span></div>
                    @foreach ($this->occupancyRanking as $row)
                        <div class="tb-row tb-grid" style="grid-template-columns:minmax(160px,1.4fr) 140px 120px 120px">
                            <span>{{ $row['car_name'] }}</span><span>{{ $row['confirmed_orders'] }}</span><span>{{ $row['booked_hours'] }}</span><span>{{ number_format($row['booked_days'], 2) }}</span>
                        </div>
                    @endforeach
                @endif
            @elseif ($this->reportType === 'rate_plan_revenue')
                @if ($this->ratePlanRevenue === [])
                    <div class="tb-empty-card"><strong>No rate plan revenue for this period</strong></div>
                @else
                    <div class="tb-head tb-grid" style="grid-template-columns:minmax(160px,1.4fr) 160px 140px"><span>Rate plan</span><span>Confirmed orders</span><span>Revenue</span></div>
                    @foreach ($this->ratePlanRevenue as $row)
                        <div class="tb-row tb-grid" style="grid-template-columns:minmax(160px,1.4fr) 160px 140px">
                            <span>{{ $row['rate_plan_name'] }}</span><span>{{ $row['confirmed_orders'] }}</span><span>{{ AdminMoney::fromCents((int) $row['revenue_cents']) }}</span>
                        </div>
                    @endforeach
                @endif
            @elseif ($this->topCountries === [])
                <div class="tb-empty-card"><strong>No country revenue for this period</strong></div>
            @else
                <div class="tb-head tb-grid" style="grid-template-columns:minmax(160px,1.4fr) 160px 140px"><span>Country</span><span>Confirmed orders</span><span>Revenue</span></div>
                @foreach ($this->topCountries as $row)
                    <div class="tb-row tb-grid" style="grid-template-columns:minmax(160px,1.4fr) 160px 140px">
                        <span>{{ $row['country'] }}</span><span>{{ $row['confirmed_orders'] }}</span><span>{{ AdminMoney::fromCents((int) $row['revenue_cents']) }}</span>
                    </div>
                @endforeach
            @endif
        </section>
    </div>
</x-filament-panels::page>
