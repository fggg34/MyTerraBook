<x-filament-panels::page>
    <style>
        html.tb-redesign .fo-title { display: none; }
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

        .fo-table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 768px) {
            .fo-title {
                font-size: 1.25rem;
            }

            .fo-car-picker {
                flex-direction: column;
                align-items: stretch;
            }

            .fo-refresh {
                width: 100%;
                min-height: 44px;
            }

            .fo-table .fo-day-num {
                font-size: 1.25rem;
            }
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

    <div class="tb-fo">
        <div class="tb-fo-top">
            <section class="tb-fo-card">
                <div class="tb-fo-label">Vehicles shown</div>
                <div class="tb-fo-chips">
                    @foreach ($this->carOptions as $carOption)
                        @if (in_array((int) $carOption['id'], array_map('intval', $this->selectedCarIds), true))
                            <button type="button" class="tb-fo-chip" wire:click="removeCar({{ (int) $carOption['id'] }})">
                                {{ $carOption['name'] }}
                                <span aria-hidden="true">×</span>
                            </button>
                        @endif
                    @endforeach
                    <label class="tb-fo-add">
                        <span>+ Add vehicle</span>
                        <select wire:change="addCar($event.target.value)" x-on:change="$el.value = ''">
                            <option value="">Add vehicle</option>
                            @foreach ($this->carOptions as $carOption)
                                @if (! in_array((int) $carOption['id'], array_map('intval', $this->selectedCarIds), true))
                                    <option value="{{ $carOption['id'] }}">{{ $carOption['name'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="tb-fo-dates">
                    <label>From<input type="date" wire:model="periodFrom" wire:change="calculateRates"></label>
                    <label>To<input type="date" wire:model="periodTo" wire:change="calculateRates"></label>
                </div>
            </section>
            <section class="tb-fo-card tb-fo-calc">
                <h2>Rates calculator</h2>
                <label>Vehicle
                    <select wire:model="calculatorCarId">
                        @foreach ($this->carOptions as $carOption)
                            <option value="{{ $carOption['id'] }}">{{ $carOption['name'] }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="tb-fo-calc-row">
                    <label>Pickup<input type="date" wire:model="pickupDate"></label>
                    <label>Days<input type="number" min="1" wire:model="rentalDays"></label>
                </div>
                <div class="tb-fo-total">
                    <span>Total</span>
                    <strong>{{ $this->calculatorTotalCents === null ? '—' : \App\Support\AdminMoney::fromCents($this->calculatorTotalCents) }}</strong>
                </div>
                <button type="button" wire:click="calculateRates">Calculate</button>
            </section>
        </div>

        @if ($this->overviewRows === [])
            <section class="tb-fo-card"><p>No vehicles selected.</p></section>
        @endif

        @foreach ($this->overviewRows as $row)
            <section class="tb-fo-card tb-fo-car">
                <header>
                    <strong>{{ $row['name'] }}</strong>
                    @unless ($row['has_base'] ?? true)
                        <span class="tb-fo-pill">No base price</span>
                    @endunless
                </header>
                <div class="tb-fo-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                @foreach ($this->displayDates as $date)
                                    <th>
                                        <span>{{ \Carbon\Carbon::parse($date)->format('D') }}</span>
                                        {{ \Carbon\Carbon::parse($date)->format('j') }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>Price</th>
                                @foreach ($row['prices'] as $priceCents)
                                    <td>{{ \App\Support\AdminMoney::fromCents((int) $priceCents) }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <th>Units free</th>
                                @foreach ($this->displayDates as $date)
                                    <td>{{ $row['units_available'] }}</td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
