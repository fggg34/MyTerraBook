<x-filament-panels::page>
    <style>
        .ts-shell {
            border: 1px solid #d9dee7;
            background: #fff;
            border-radius: 6px;
            padding: 10px;
        }

        .ts-toolbar {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr)) auto auto;
            gap: 8px;
            align-items: end;
            margin-bottom: 12px;
        }

        .ts-label {
            font-size: 11px;
            color: #607080;
            margin-bottom: 4px;
            display: block;
        }

        .ts-field {
            width: 100%;
            border: 1px solid #cfd7e2;
            border-radius: 2px;
            background: #fff;
            font-size: 12px;
            padding: 6px 8px;
            color: #1f2b37;
            min-height: 34px;
        }

        .ts-button {
            border: 1px solid #d0d7e2;
            background: #f7f9fc;
            color: #2c3a48;
            padding: 7px 10px;
            border-radius: 2px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            min-height: 34px;
        }

        .ts-button--primary {
            background: #eef4fb;
            border-color: #c7d5ea;
        }

        .ts-tabs {
            display: flex;
            gap: 14px;
            border-bottom: 1px solid #e4e9f0;
            margin-bottom: 14px;
            padding: 0 2px;
        }

        .ts-tab {
            border: 0;
            background: transparent;
            color: #566b80;
            padding: 8px 0;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }

        .ts-tab.is-active {
            color: #45a06a;
            border-bottom-color: #45a06a;
        }

        .ts-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #2b3642;
            margin-bottom: 8px;
        }

        .ts-demand-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 14px;
        }

        .ts-demand-card {
            border: 1px solid #e4e9f0;
            background: #fff;
            border-radius: 3px;
            padding: 8px;
        }

        .ts-demand-date {
            font-size: 11px;
            font-weight: 700;
            color: #4c5d6f;
            margin-bottom: 8px;
        }

        .ts-demand-stat {
            font-size: 12px;
            color: #2d3c4a;
            margin-bottom: 3px;
        }

        .ts-demand-stat strong {
            color: #45a06a;
            font-size: 30px;
            line-height: 1;
            font-weight: 500;
            margin-right: 5px;
            vertical-align: middle;
        }

        .ts-bottom-grid {
            display: grid;
            grid-template-columns: 3fr 1.4fr;
            gap: 10px;
        }

        .ts-panel {
            border: 1px solid #e4e9f0;
            border-radius: 3px;
            background: #fff;
            padding: 10px;
        }

        .ts-avg-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
        }

        .ts-avg-card {
            border: 1px solid #e4e9f0;
            border-radius: 3px;
            padding: 10px;
            background: #fdfefe;
        }

        .ts-avg-key {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            color: #6c7f92;
            margin-bottom: 6px;
        }

        .ts-avg-value {
            font-size: 34px;
            line-height: 1;
            font-weight: 500;
            color: #2f9b47;
        }

        .ts-ref-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            gap: 8px;
        }

        .ts-ref-host {
            font-size: 12px;
            font-weight: 700;
            color: #2d3c4a;
            word-break: break-word;
        }

        .ts-ref-visitors {
            font-size: 11px;
            color: #6f8194;
            margin-top: 2px;
        }

        .ts-table-wrap {
            border: 1px solid #e4e9f0;
            border-radius: 3px;
            overflow-x: auto;
        }

        .ts-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .ts-table th {
            background: #45a06a;
            color: #fff;
            font-weight: 700;
            text-align: left;
            padding: 8px;
            border: 1px solid #1f6f95;
        }

        .ts-table td {
            border: 1px solid #d6dce5;
            padding: 8px;
            color: #1f2b37;
            background: #fff;
        }

        .ts-empty {
            font-size: 12px;
            color: #6f8194;
        }

        @media (max-width: 1200px) {
            .ts-toolbar {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ts-demand-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .ts-bottom-grid,
            .ts-avg-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="ts-shell">
        <div class="ts-toolbar">
            <div>
                <label class="ts-label" for="TsFrom">From</label>
                <input id="TsFrom" type="date" class="ts-field" wire:model.live="from" />
            </div>

            <div>
                <label class="ts-label" for="TsTo">To</label>
                <input id="TsTo" type="date" class="ts-field" wire:model.live="to" />
            </div>

            <div>
                <label class="ts-label" for="TsTrackingDates">Tracking Dates</label>
                <select id="TsTrackingDates" class="ts-field" wire:model.live="trackingDates">
                    <option value="custom">Custom Range</option>
                    <option value="today">Today</option>
                    <option value="last_7_days">Last 7 Days</option>
                    <option value="last_30_days">Last 30 Days</option>
                    <option value="this_month">This Month</option>
                </select>
            </div>

            <div>
                <label class="ts-label" for="TsCountry">Filter by Country</label>
                <select id="TsCountry" class="ts-field" wire:model.live="country">
                    <option value="">All Countries</option>
                    @foreach ($this->countryOptions as $countryOption)
                        <option value="{{ $countryOption }}">{{ $countryOption }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ts-label" for="TsReferrer">Filter by Referrer</label>
                <select id="TsReferrer" class="ts-field" wire:model.live="referrer">
                    <option value="">All Referrers</option>
                    @foreach ($this->referrerOptions as $referrerOption)
                        <option value="{{ $referrerOption }}">{{ $referrerOption }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ts-label" for="TsSearch">Filter Results</label>
                <input id="TsSearch" type="text" class="ts-field" wire:model.live.debounce.300ms="query" placeholder="Search..." />
            </div>

            <button type="button" class="ts-button" wire:click="clearFilters">Clear</button>
            <a class="ts-button ts-button--primary" href="{{ $this->trackingSettingsUrl }}">Tracking Settings</a>
        </div>

        <div class="ts-tabs">
            <button type="button" class="ts-tab {{ $this->activeTab === 'visitors' ? 'is-active' : '' }}" wire:click="$set('activeTab', 'visitors')">Visitors</button>
            <button type="button" class="ts-tab {{ $this->activeTab === 'conversion_rates' ? 'is-active' : '' }}" wire:click="$set('activeTab', 'conversion_rates')">Conversion Rates</button>
        </div>

        @if ($this->activeTab === 'visitors')
            <section>
                <div class="ts-section-title">Most Demanded Days</div>

                @if ($this->mostDemandedDays === [])
                    <p class="ts-empty">No tracking events found for the selected filters.</p>
                @else
                    <div class="ts-demand-grid">
                        @foreach ($this->mostDemandedDays as $day)
                            <article class="ts-demand-card">
                                <div class="ts-demand-date">{{ \Carbon\Carbon::parse($day['date'])->format('D, d/m/Y') }}</div>
                                <div class="ts-demand-stat"><strong>{{ $day['requests'] }}</strong> Request(s)</div>
                                <div class="ts-demand-stat"><strong style="font-size: 28px;">{{ $day['visitors'] }}</strong> Visitor(s)</div>
                            </article>
                        @endforeach
                    </div>
                @endif

                <div class="ts-bottom-grid">
                    <section class="ts-panel">
                        <div class="ts-section-title">Average Values</div>
                        <div class="ts-avg-grid">
                            <article class="ts-avg-card">
                                <div class="ts-avg-key">Total Visitors</div>
                                <div class="ts-avg-value">{{ $this->averageValues['total_visitors'] ?? 0 }}</div>
                            </article>
                            <article class="ts-avg-card">
                                <div class="ts-avg-key">Total Bookings</div>
                                <div class="ts-avg-value">{{ $this->averageValues['total_bookings'] ?? 0 }}</div>
                            </article>
                            <article class="ts-avg-card">
                                <div class="ts-avg-key">Average Length of Rent</div>
                                <div class="ts-avg-value">{{ number_format((float) ($this->averageValues['average_length_of_rent'] ?? 0), 1) }}</div>
                            </article>
                            <article class="ts-avg-card">
                                <div class="ts-avg-key">Average Conversion Rate</div>
                                <div class="ts-avg-value">{{ number_format((float) ($this->averageValues['average_conversion_rate'] ?? 0), 2) }}%</div>
                            </article>
                        </div>
                    </section>

                    <aside class="ts-panel">
                        <div class="ts-section-title">Best Referrers</div>
                        @if ($this->bestReferrers === [])
                            <p class="ts-empty">No referrer data.</p>
                        @else
                            <ul class="ts-ref-list">
                                @foreach ($this->bestReferrers as $referrerRow)
                                    <li>
                                        <div class="ts-ref-host">{{ strtoupper($referrerRow['referrer']) }}</div>
                                        <div class="ts-ref-visitors">{{ $referrerRow['visitors'] }} Visitor(s)</div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </aside>
                </div>
            </section>
        @else
            <section>
                <div class="ts-section-title">Daily Conversion Rates</div>
                @if ($this->conversionRates === [])
                    <p class="ts-empty">No conversion-rate data found for the selected filters.</p>
                @else
                    <div class="ts-table-wrap">
                        <table class="ts-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Visitors</th>
                                    <th>Bookings</th>
                                    <th>Conversion Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->conversionRates as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row['date'])->format('D, d/m/Y') }}</td>
                                        <td>{{ $row['visitors'] }}</td>
                                        <td>{{ $row['bookings'] }}</td>
                                        <td>{{ number_format($row['conversion_rate'], 2) }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endif
    </section>
</x-filament-panels::page>
