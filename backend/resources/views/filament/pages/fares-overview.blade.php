<x-filament-panels::page>
    <style>
        .fo-card {
            border: 1px solid #d9dee7;
            border-radius: 6px;
            background: #fff;
            padding: 14px;
        }

        .fo-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e2a35;
            margin-bottom: 12px;
        }

        .fo-top-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .fo-label {
            font-size: 13px;
            font-weight: 700;
            color: #2c3642;
            margin-bottom: 8px;
        }

        .fo-muted {
            font-size: 12px;
            color: #607080;
        }

        .fo-car-picker {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 8px;
            align-items: end;
        }

        .fo-field {
            box-sizing: border-box;
            width: 100%;
            min-width: 0;
            border: 1px solid #cfd7e2;
            border-radius: 2px;
            background: #fff;
            font-size: 12px;
            padding: 6px 8px;
            color: #1f2b37;
        }

        .fo-multi {
            min-height: 92px;
        }

        .fo-refresh {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 2px;
            background: #334e68;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .fo-calc-row {
            display: grid;
            grid-template-columns: 2fr 1.15fr 0.7fr auto;
            gap: 8px;
            align-items: end;
        }

        .fo-button {
            border: 0;
            border-radius: 2px;
            background: #334e68;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 8px 14px;
            cursor: pointer;
        }

        .fo-car-block {
            border: 1px solid #d9dee7;
            border-radius: 6px;
            background: #fff;
            padding: 10px;
        }

        .fo-car-name {
            font-size: 24px;
            font-weight: 700;
            color: #1f2b37;
            margin-bottom: 10px;
        }

        .fo-overview-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 160px;
            gap: 10px;
            align-items: start;
        }

        .fo-table-wrap {
            overflow-x: auto;
        }

        .fo-table {
            width: 100%;
            min-width: 980px;
            border-collapse: collapse;
            font-size: 12px;
        }

        .fo-table thead th {
            border: 1px solid #243b53;
            background: #334e68;
            color: #fff;
            text-align: center;
            font-weight: 700;
            padding: 8px 5px;
        }

        .fo-table thead th.fo-date-head {
            width: 108px;
            background: #334e68;
            border-color: #243b53;
        }

        .fo-table .fo-day-num {
            font-size: 31px;
            line-height: 1;
            font-weight: 700;
            margin: 2px 0 1px;
        }

        .fo-table .fo-day-month {
            font-size: 10px;
            letter-spacing: 0.3px;
            opacity: 0.9;
        }

        .fo-table tbody td {
            border: 1px solid #d6dce5;
            padding: 6px 5px;
            text-align: center;
            background: #fff;
            color: #1f2b37;
        }

        .fo-table tbody td.fo-row-label {
            background: #f0f3f7;
            text-align: left;
            font-weight: 700;
            width: 108px;
        }

        .fo-table tbody tr.fo-units-row td {
            background: #f6f8fb;
        }

        .fo-period {
            border: 1px solid #d9dee7;
            border-radius: 4px;
            padding: 10px;
            background: #f9fbfd;
        }

        .fo-period-title {
            font-size: 12px;
            font-weight: 700;
            color: #2b3642;
            margin-bottom: 8px;
        }

        .fo-period-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
        }

        @media (max-width: 1100px) {
            .fo-top-grid {
                grid-template-columns: 1fr;
            }

            .fo-calc-row {
                grid-template-columns: 1fr;
            }

            .fo-overview-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="fo-title">Fares Overview</div>

    <section class="fo-card">
        <div class="fo-top-grid">
            <div>
                <div class="fo-label">Car</div>
                <div class="fo-car-picker">
                    <select wire:model="selectedCarIds" multiple class="fo-field fo-multi">
                        @foreach ($this->carOptions as $carOption)
                            <option value="{{ $carOption['id'] }}">{{ $carOption['name'] }}</option>
                        @endforeach
                    </select>
                    <button class="fo-refresh" type="button" wire:click="calculateRates" aria-label="Refresh rates">
                        ↻
                    </button>
                </div>
            </div>

            <div>
                <div class="fo-label">Rates Calculator</div>
                <div class="fo-calc-row">
                    <div>
                        <label class="fo-muted">Car</label>
                        <select wire:model="calculatorCarId" class="fo-field">
                            @foreach ($this->carOptions as $carOption)
                                <option value="{{ $carOption['id'] }}">{{ $carOption['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="fo-muted">Pickup at</label>
                        <input type="date" wire:model="pickupDate" class="fo-field" />
                    </div>
                    <div>
                        <label class="fo-muted">Days</label>
                        <input type="number" min="1" wire:model="rentalDays" class="fo-field" />
                    </div>
                    <button class="fo-button" type="button" wire:click="calculateRates">Calculate</button>
                </div>
            </div>
        </div>
    </section>

    @if ($this->overviewRows === [])
        <section class="fo-card" style="margin-top: 14px;">
            <div class="fo-muted">No cars selected for overview.</div>
        </section>
    @endif

    @foreach ($this->overviewRows as $row)
        <section class="fo-car-block" style="margin-top: 14px;">
            <div class="fo-label" style="margin-bottom: 10px;">🚘 {{ $row['name'] }}</div>

            <div class="fo-overview-grid">
                <div class="fo-table-wrap">
                    <table class="fo-table">
                        <thead>
                            <tr>
                                <th class="fo-date-head">{{ \Carbon\Carbon::parse($this->periodFrom)->format('d/m/Y') }}</th>
                                @foreach ($this->displayDates as $date)
                                    <th>
                                        <div>{{ \Carbon\Carbon::parse($date)->format('D') }}</div>
                                        <div class="fo-day-num">{{ \Carbon\Carbon::parse($date)->format('j') }}</div>
                                        <div class="fo-day-month">{{ strtoupper(\Carbon\Carbon::parse($date)->format('M')) }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fo-row-label">Price</td>
                                @foreach ($row['prices'] as $priceCents)
                                    <td>{{ $this->currencyCode }} {{ number_format((int) floor($priceCents / 100), 0, '.', '') }}</td>
                                @endforeach
                            </tr>
                            <tr class="fo-units-row">
                                <td class="fo-row-label">Units Available</td>
                                @foreach ($this->displayDates as $date)
                                    <td>{{ $row['units_available'] }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

                <aside class="fo-period">
                    <div class="fo-period-title">Select Period</div>
                    <div class="fo-period-grid">
                        <div>
                            <label class="fo-muted">From</label>
                            <input type="date" wire:model="periodFrom" class="fo-field" />
                        </div>
                        <div>
                            <label class="fo-muted">To</label>
                            <input type="date" wire:model="periodTo" class="fo-field" />
                        </div>
                    </div>
                </aside>
            </div>
        </section>
    @endforeach
</x-filament-panels::page>
