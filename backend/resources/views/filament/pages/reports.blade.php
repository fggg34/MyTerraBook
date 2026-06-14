<x-filament-panels::page>
    <style>
        .ir-reports-card {
            border: 1px solid #d9dee7;
            border-radius: 6px;
            background: #fff;
            padding: 12px;
        }

        .ir-reports-controls {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .ir-reports-field {
            width: 100%;
            border: 1px solid #cfd7e2;
            border-radius: 2px;
            background: #fff;
            font-size: 12px;
            padding: 6px 8px;
            color: #1f2b37;
        }

        .ir-reports-label {
            font-size: 12px;
            color: #607080;
            margin-bottom: 5px;
            display: block;
        }

        .ir-reports-period {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .ir-reports-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .ir-reports-table th {
            background: #45a06a;
            color: #fff;
            text-align: left;
            border: 1px solid #1f6f95;
            padding: 8px;
            font-weight: 700;
        }

        .ir-reports-table td {
            border: 1px solid #d6dce5;
            padding: 8px;
            color: #1f2b37;
            background: #fff;
        }

        .ir-reports-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }

        .ir-reports-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .ir-reports-summary-item {
            border: 1px solid #d6dce5;
            border-radius: 4px;
            background: #f8fafc;
            padding: 10px;
        }

        .ir-reports-summary-key {
            font-size: 11px;
            color: #607080;
            margin-bottom: 4px;
        }

        .ir-reports-summary-value {
            font-size: 18px;
            font-weight: 700;
            color: #1f2b37;
            line-height: 1.2;
        }

        .ir-reports-empty {
            font-size: 12px;
            color: #607080;
        }

        @media (max-width: 900px) {
            .ir-reports-controls {
                grid-template-columns: 1fr;
            }

            .ir-reports-period {
                grid-template-columns: 1fr;
            }

            .ir-reports-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="ir-reports-card">
        <div class="ir-reports-controls">
            <div>
                <label class="ir-reports-label" for="ReportTypeSelect">Select Report Type</label>
                <select id="ReportTypeSelect" class="ir-reports-field" wire:model.live="reportType">
                    <option value="occupancy_ranking">Occupancy Ranking</option>
                    <option value="revenue">Revenue</option>
                    <option value="rate_plan_revenue">Rate Plans Revenue</option>
                    <option value="top_countries">Top Countries</option>
                </select>
            </div>

            <div>
                <span class="ir-reports-label">Period</span>
                <div class="ir-reports-period">
                    <input type="date" class="ir-reports-field" wire:model.live="from" />
                    <input type="date" class="ir-reports-field" wire:model.live="to" />
                </div>
            </div>
        </div>

        @if ($this->reportType === 'occupancy_ranking')
            @if ($this->occupancyRanking === [])
                <p class="ir-reports-empty">No occupancy data for the selected period.</p>
            @else
                <table class="ir-reports-table">
                    <thead>
                        <tr>
                            <th>Car</th>
                            <th>Confirmed Orders</th>
                            <th>Booked Hours</th>
                            <th>Booked Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->occupancyRanking as $row)
                            <tr>
                                <td>{{ $row['car_name'] }}</td>
                                <td>{{ $row['confirmed_orders'] }}</td>
                                <td>{{ $row['booked_hours'] }}</td>
                                <td>{{ number_format($row['booked_days'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @elseif ($this->reportType === 'revenue')
            <div class="ir-reports-summary-grid">
                <div class="ir-reports-summary-item">
                    <div class="ir-reports-summary-key">Confirmed Orders</div>
                    <div class="ir-reports-summary-value">{{ $this->revenueSummary['confirmed_orders'] ?? 0 }}</div>
                </div>
                <div class="ir-reports-summary-item">
                    <div class="ir-reports-summary-key">Revenue</div>
                    <div class="ir-reports-summary-value">{{ $this->revenueSummary['revenue'] ?? '0.00' }}</div>
                </div>
                <div class="ir-reports-summary-item">
                    <div class="ir-reports-summary-key">Average Order Value</div>
                    <div class="ir-reports-summary-value">{{ $this->revenueSummary['average_order_value'] ?? '0.00' }}</div>
                </div>
            </div>
        @elseif ($this->reportType === 'rate_plan_revenue')
            @if ($this->ratePlanRevenue === [])
                <p class="ir-reports-empty">No rate plan revenue data for the selected period.</p>
            @else
                <table class="ir-reports-table">
                    <thead>
                        <tr>
                            <th>Rate Plan</th>
                            <th>Confirmed Orders</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->ratePlanRevenue as $row)
                            <tr>
                                <td>{{ $row['rate_plan_name'] }}</td>
                                <td>{{ $row['confirmed_orders'] }}</td>
                                <td>{{ $row['revenue'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @elseif ($this->reportType === 'top_countries')
            @if ($this->topCountries === [])
                <p class="ir-reports-empty">No country revenue data for the selected period.</p>
            @else
                <table class="ir-reports-table">
                    <thead>
                        <tr>
                            <th>Country</th>
                            <th>Confirmed Orders</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->topCountries as $row)
                            <tr>
                                <td>{{ $row['country'] }}</td>
                                <td>{{ $row['confirmed_orders'] }}</td>
                                <td>{{ $row['revenue'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </section>
</x-filament-panels::page>
