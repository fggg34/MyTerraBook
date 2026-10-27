<x-filament-widgets::widget>
    <div class="tb-dash">
        @if ($kpis !== [])
            <div class="tb-kpis">
                @foreach ($kpis as $kpi)
                    <a class="tb-kpi" href="{{ $kpi['url'] }}">
                        <span class="tb-kpi-label">
                            <span @class(['tb-kpi-tile', 'is-'.$kpi['tile']])>
                                <img src="{{ asset('admin-redesign/'.$kpi['icon']) }}" alt="" width="16" height="16">
                            </span>
                            {{ $kpi['label'] }}
                        </span>
                        <strong>{{ $kpi['value'] }}</strong>
                        <span class="tb-kpi-meta">{{ $kpi['meta'] }}</span>
                        <span @class(['tb-kpi-link', 'is-'.$kpi['linkTone']])>{{ $kpi['link'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="tb-dash-row">
            <section class="tb-card tb-attention">
                <header>
                    <h2>Needs your attention</h2>
                    @if ($todoCount > 0)
                        <span class="tb-pill is-warn">{{ $todoCount }} to do</span>
                    @endif
                </header>
                @foreach ($attention as $item)
                    <div class="tb-attn">
                        <span @class(['tb-kpi-tile', 'is-'.$item['tone']])>
                            <img src="{{ asset('admin-redesign/'.$item['icon']) }}" alt="" width="16" height="16">
                        </span>
                        <span>
                            <strong>{{ $item['title'] }}</strong>
                            <small>{{ $item['meta'] }}</small>
                        </span>
                        <a href="{{ $item['url'] }}">{{ $item['action'] }}</a>
                    </div>
                @endforeach
            </section>

            <div class="tb-dash-side">
                <section class="tb-card tb-chart">
                    <header>
                        <h2>30-day overview</h2>
                        <span>Confirmed only</span>
                    </header>
                    <div class="tb-bars">
                        @foreach ($bars as $bar)
                            <span @style(['height: '.$bar['height'].'px', 'background: '.($bar['active'] ? '#4474a4' : '#dbe3ec')])></span>
                        @endforeach
                    </div>
                    <div class="tb-chart-labels">
                        <span>{{ $chartStart }}</span>
                        <span>{{ $chartEnd }}</span>
                    </div>
                </section>
                <section class="tb-card">
                    <h2>Top countries (30 days)</h2>
                    @if (count($countries) === 0)
                        <p class="tb-empty">No country data yet. It appears after the first tracked visits.</p>
                    @else
                        <ul class="tb-countries">
                            @foreach ($countries as $country)
                                <li>
                                    <strong>{{ $country['country'] }}</strong>
                                    <span>{{ $country['confirmed_orders'] }} orders · {{ $country['revenue'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>
            </div>
        </div>

        <section class="tb-card tb-orders">
            <header>
                <h2>Recent orders</h2>
                <div class="tb-tabs">
                    <button type="button" wire:click="setOrderTab('all')" @class(['is-active' => $orderTab === 'all'])>All <em>{{ $carCount + $stayCount }}</em></button>
                    <button type="button" wire:click="setOrderTab('cars')" @class(['is-active' => $orderTab === 'cars'])>Cars <em>{{ $carCount }}</em></button>
                    <button type="button" wire:click="setOrderTab('stays')" @class(['is-active' => $orderTab === 'stays'])>Guest houses <em>{{ $stayCount }}</em></button>
                </div>
                <a class="tb-orders-all" href="{{ $orderTab === 'stays' ? $staysUrl : $ordersUrl }}">
                    <img src="{{ asset('admin-redesign/icon-btn-external.svg') }}" alt="" width="16" height="16">
                    View all reservations
                </a>
            </header>
            <div class="tb-table">
                <div class="tb-tr tb-th">
                    <span>Reference</span>
                    <span>Customer</span>
                    <span>Booked item</span>
                    <span>Dates</span>
                    <span>Status</span>
                    <span>Total</span>
                </div>
                @forelse ($rows as $row)
                    <a class="tb-tr" href="{{ $row['url'] }}">
                        <span class="tb-ref">{{ $row['reference'] }}</span>
                        <span>
                            <strong>{{ $row['customer'] }}</strong>
                            @if ($row['email'] !== '')
                                <small>{{ $row['email'] }}</small>
                            @endif
                        </span>
                        <span>{{ $row['item'] }}</span>
                        <span>{{ $row['dates'] }}</span>
                        <span><i @class(['tb-pill', 'is-'.$row['tone']])>{{ $row['status'] }}</i></span>
                        <span class="tb-total">{{ $row['total'] }}</span>
                    </a>
                @empty
                    <p class="tb-empty">No bookings yet.</p>
                @endforelse
            </div>
        </section>
    </div>

    <style>
        .tb-dash { display: flex; flex-direction: column; gap: 20px; }
        .tb-kpis { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px; }
        .tb-kpi, .tb-card {
            border: 1px solid #dbe3ec;
            border-radius: 12px;
            background: #fff;
            color: inherit;
            text-decoration: none;
        }
        .tb-kpi { display: flex; flex-direction: column; gap: 10px; padding: 16px; }
        .tb-kpi-label, .tb-attn strong { display: flex; gap: 8px; align-items: center; }
        .tb-kpi-label { color: #5b6b7d; font-size: 12px; font-weight: 600; line-height: 16px; }
        .tb-kpi-tile { display: grid; width: 28px; height: 28px; place-items: center; border-radius: 8px; background: #e8eff7; }
        .tb-kpi-tile.is-warn { background: #fdf1dc; }
        .tb-kpi-tile.is-bad { background: #fde7e5; }
        .tb-kpi-tile img { width: 16px; height: 16px; }
        .tb-kpi strong { font-family: Manrope, sans-serif; font-size: 26px; font-weight: 800; line-height: 32px; letter-spacing: -0.4px; color: #041e37; }
        .tb-kpi-meta, .tb-attn small, .tb-empty, .tb-chart header span { color: #5b6b7d; font-size: 12px; line-height: 16px; }
        .tb-kpi-link { font-size: 12px; font-weight: 600; line-height: 16px; }
        .tb-kpi-link.is-warn { color: #9a5b00; }
        .tb-kpi-link.is-steel { color: #4474a4; }
        .tb-kpi-link.is-muted { color: #5b6b7d; }
        .tb-dash-row { display: grid; grid-template-columns: minmax(0, 1fr) 440px; gap: 16px; align-items: start; }
        .tb-card { overflow: hidden; }
        .tb-card header, .tb-chart, .tb-dash-side .tb-card { padding: 16px 20px; }
        .tb-card h2 { margin: 0; font-family: Manrope, sans-serif; font-size: 15px; font-weight: 700; line-height: 22px; color: #041e37; }
        .tb-attention header, .tb-orders header, .tb-chart header { display: flex; align-items: center; gap: 12px; }
        .tb-attention header h2, .tb-chart header h2 { flex: 1; }
        .tb-chart header { padding: 0; }
        .tb-chart header span { color: #8a99a9; }
        .tb-attn { display: grid; grid-template-columns: 30px minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 12px 20px; border-top: 1px solid #eaf0f5; }
        .tb-attn strong, .tb-tr strong { display: block; color: #0d1b2a; font-size: 14px; font-weight: 500; line-height: 20px; }
        .tb-attn small { display: block; }
        .tb-attn a, .tb-orders-all {
            padding: 6px 12px;
            border: 1px solid #dbe3ec;
            border-radius: 6px;
            background: #fff;
            color: #0d1b2a;
            font-size: 12px;
            font-weight: 600;
            line-height: 16px;
            text-decoration: none;
        }
        .tb-orders-all { display: inline-flex; align-items: center; gap: 6px; margin-left: auto; }
        .tb-bars { display: flex; gap: 5px; align-items: flex-end; height: 120px; margin-top: 12px; }
        .tb-bars span { flex: 1; border-radius: 2px; }
        .tb-chart-labels { display: flex; justify-content: space-between; margin-top: 8px; color: #8a99a9; font-size: 12px; }
        .tb-dash-side { display: flex; flex-direction: column; gap: 16px; }
        .tb-countries { margin: 8px 0 0; padding: 0; list-style: none; }
        .tb-countries li { display: flex; flex-direction: column; padding: 8px 0; border-top: 1px solid #eaf0f5; }
        .tb-tabs { display: flex; gap: 2px; padding: 3px; border-radius: 9px; background: #eaf0f5; }
        .tb-tabs button { display: inline-flex; gap: 6px; align-items: center; padding: 7px 12px; border: 0; border-radius: 7px; background: transparent; color: #5b6b7d; font-size: 14px; font-weight: 600; cursor: pointer; }
        .tb-tabs button.is-active { background: #fff; color: #0d1b2a; box-shadow: 0 1px 1px rgb(4 30 55 / 10%); }
        .tb-tabs em { font-style: normal; color: #8a99a9; }
        .tb-table { border-top: 1px solid #eaf0f5; }
        .tb-tr { display: grid; grid-template-columns: 170px 180px minmax(0, 1fr) 180px 120px 130px; gap: 12px; align-items: center; padding: 10px 16px; color: #0d1b2a; font-size: 14px; text-decoration: none; }
        .tb-th { background: #f7f9fc; color: #5b6b7d; font-size: 12px; font-weight: 600; }
        .tb-tr + .tb-tr { border-top: 1px solid #eaf0f5; }
        .tb-ref { color: #4474a4; font-weight: 600; }
        .tb-total { text-align: right; font-variant-numeric: tabular-nums; }
        .tb-pill { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-style: normal; font-weight: 600; }
        .tb-pill.is-warn { background: #fdf1dc; color: #9a5b00; }
        .tb-pill.is-ok { background: #e3f3ea; color: #1f7a4d; }
        .tb-pill.is-bad { background: #fde7e5; color: #b42318; }
        .tb-pill.is-info { background: #e8eff7; color: #4474a4; }
        .tb-empty { margin: 8px 0 0; }
        @media (max-width: 1100px) {
            .tb-kpis, .tb-dash-row, .tb-tr { grid-template-columns: 1fr; }
            .tb-dash-side { width: auto; }
        }
    </style>
</x-filament-widgets::widget>
