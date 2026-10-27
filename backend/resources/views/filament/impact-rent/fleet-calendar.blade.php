<x-filament-panels::page>
    @php
        $board = $this->board();
    @endphp

    <style>
        .ir-fleet { display: flex; flex-direction: column; gap: 1rem; }
        .ir-fleet-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .ir-fleet-nav, .ir-fleet-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
        .ir-fleet-nav h2 { margin: 0 0 0 0.35rem; font-size: 1.125rem; font-weight: 600; color: #0f172a; }
        .ir-fleet-btn, .ir-fleet-select {
            height: 2.25rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background: #fff;
            color: #0f172a;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .ir-fleet-btn { padding: 0 0.75rem; cursor: pointer; }
        .ir-fleet-btn:hover { background: #f8fafc; }
        .ir-fleet-select { min-width: 13rem; padding: 0 0.75rem; }
        .ir-fleet-create {
            display: inline-flex;
            align-items: center;
            height: 2.75rem;
            padding: 0 1.1rem;
            border-radius: 0.5rem;
            background: #059669;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
        }
        .ir-fleet-create:hover { background: #047857; }
        .ir-fleet-legend { display: flex; flex-wrap: wrap; gap: 0.75rem; color: #64748b; font-size: 0.75rem; }
        .ir-fleet-legend span { display: inline-flex; align-items: center; gap: 0.35rem; }
        .ir-swatch { width: 1.25rem; height: 0.75rem; border-radius: 0.25rem; border: 1px solid transparent; }
        .ir-swatch-quote { background: #fef3c7; border-color: #fcd34d; }
        .ir-swatch-confirmed { background: #e0f2fe; border-color: #7dd3fc; }
        .ir-swatch-active { background: #fef08a; border-color: #facc15; }
        .ir-swatch-returned { background: #e2e8f0; border-color: #cbd5e1; }
        .ir-swatch-noshow { background: #fee2e2; border-color: #fca5a5; }
        .ir-swatch-block {
            border-color: #94a3b8;
            background: repeating-linear-gradient(-45deg, #e2e8f0, #e2e8f0 4px, #cbd5e1 4px, #cbd5e1 8px);
        }
        .ir-fleet-board {
            overflow: auto;
            max-height: 72vh;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .ir-fleet-head, .ir-fleet-group, .ir-fleet-row { display: flex; }
        .ir-fleet-head { position: sticky; top: 0; z-index: 30; border-bottom: 1px solid #e2e8f0; background: #f1f5f9; }
        .ir-fleet-side {
            position: sticky;
            left: 0;
            z-index: 20;
            flex: 0 0 auto;
            box-sizing: border-box;
            border-right: 1px solid #e2e8f0;
            background: #fff;
        }
        .ir-fleet-head .ir-fleet-side { z-index: 40; background: #f1f5f9; display: flex; flex-direction: column; justify-content: center; padding: 0.4rem 0.75rem; }
        .ir-fleet-kicker { font-size: 0.75rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: #475569; }
        .ir-fleet-count { font-size: 11px; color: #64748b; }
        .ir-month {
            overflow: hidden;
            border-right: 1px solid #cbd5e1;
            background: #64748b;
            color: #fff;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.04em;
            padding: 0.25rem 0;
        }
        .ir-day {
            display: flex;
            flex-direction: column;
            align-items: center;
            box-sizing: border-box;
            border-right: 1px solid #e2e8f0;
            padding: 0.2rem 0;
            font-size: 11px;
            font-variant-numeric: tabular-nums;
            color: #334155;
        }
        .ir-day small { font-size: 9px; text-transform: uppercase; color: #64748b; }
        .ir-day.is-weekend { background: rgba(226, 232, 240, 0.7); }
        .ir-day.is-today { background: #2563eb; color: #fff; font-weight: 600; }
        .ir-day.is-today small { color: #dbeafe; }
        .ir-fleet-group { border-bottom: 1px solid #e2e8f0; background: #f8fafc; }
        .ir-fleet-group .ir-fleet-side { background: #f8fafc; padding: 0.4rem 0.75rem; }
        .ir-fleet-group strong { font-size: 0.875rem; color: #1e293b; }
        .ir-fleet-row { border-bottom: 1px solid #e2e8f0; }
        .ir-fleet-row.is-inactive { opacity: 0.6; }
        .ir-fleet-plate { display: flex; align-items: center; gap: 0.5rem; height: 100%; padding: 0 0.75rem; }
        .ir-dot { width: 0.5rem; height: 0.5rem; border-radius: 999px; background: #0164a3; flex: 0 0 auto; }
        .ir-dot.is-off { background: #cbd5e1; }
        .ir-fleet-plate p { margin: 0; }
        .ir-fleet-plate .title { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 13px; font-weight: 600; letter-spacing: 0.03em; }
        .ir-fleet-plate .sub { font-size: 11px; color: #64748b; }
        .ir-lane { position: relative; }
        .ir-grid { position: absolute; inset: 0; display: flex; }
        .ir-grid i { display: block; height: 100%; border-right: 1px solid #f1f5f9; box-sizing: border-box; }
        .ir-grid i.is-weekend { background: #f8fafc; }
        .ir-grid i.is-today { background: #eff6ff; }
        .ir-bar, .ir-block {
            position: absolute;
            display: flex;
            align-items: center;
            overflow: hidden;
            border-radius: 0.375rem;
            border: 1px solid transparent;
            padding: 0 0.35rem;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .ir-bar { height: 30px; cursor: pointer; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06); z-index: 2; }
        .ir-bar-quote { background: #fef3c7; border-color: #fcd34d; color: #78350f; }
        .ir-bar-confirmed { background: #e0f2fe; border-color: #7dd3fc; color: #0c4a6e; }
        .ir-bar-active { background: #fef08a; border-color: #facc15; color: #713f12; }
        .ir-bar-returned { background: #e2e8f0; border-color: #cbd5e1; color: #334155; }
        .ir-bar-noshow { background: #fee2e2; border-color: #fca5a5; color: #7f1d1d; }
        .ir-block {
            z-index: 1;
            color: #334155;
            border-color: rgba(148, 163, 184, 0.7);
            background: repeating-linear-gradient(-45deg, #e2e8f0, #e2e8f0 4px, #cbd5e1 4px, #cbd5e1 8px);
        }
        .ir-fleet-empty { margin: 0; padding: 3rem 1rem; text-align: center; color: #64748b; font-size: 0.875rem; }
        .ir-dialog-back {
            position: fixed;
            inset: 0;
            z-index: 70;
            background: rgba(15, 23, 42, 0.35);
        }
        .ir-dialog {
            position: fixed;
            z-index: 80;
            top: 50%;
            left: 50%;
            width: min(28rem, calc(100vw - 2rem));
            transform: translate(-50%, -50%);
            border-radius: 0.75rem;
            background: #fff;
            padding: 1.25rem;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
        }
        .ir-dialog h3 { margin: 0; font-size: 1.05rem; color: #0f172a; }
        .ir-dialog .who { margin: 0.25rem 0 0; color: #64748b; font-size: 0.875rem; }
        .ir-dialog dl { margin: 1rem 0 0; display: grid; gap: 0.75rem; }
        .ir-dialog .line { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.875rem; }
        .ir-dialog dt { color: #64748b; }
        .ir-dialog dd { margin: 0; text-align: right; font-weight: 600; color: #0f172a; }
        .ir-dialog dd small { display: block; font-weight: 400; color: #64748b; }
        .ir-dialog .total { border-top: 1px solid #e2e8f0; padding-top: 0.75rem; }
        .ir-dialog-actions { display: flex; justify-content: space-between; gap: 0.5rem; margin-top: 1.25rem; }
        .ir-dialog-open {
            display: inline-flex;
            align-items: center;
            height: 2.25rem;
            padding: 0 0.9rem;
            border-radius: 0.5rem;
            background: #0164a3;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
        }
        .ir-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.1rem 0.5rem;
            font-size: 11px;
            font-weight: 700;
        }
    </style>

    <div class="ir-fleet">
        <div class="ir-fleet-toolbar">
            <div class="ir-fleet-nav">
                <button type="button" class="ir-fleet-btn" wire:click="shift(-30)" aria-label="Earlier">Earlier</button>
                <button type="button" class="ir-fleet-btn" wire:click="jumpToday">Today</button>
                <button type="button" class="ir-fleet-btn" wire:click="shift(30)" aria-label="Later">Later</button>
                <h2>{{ $board['rangeLabel'] }}</h2>
            </div>
            <div class="ir-fleet-tools">
                <select class="ir-fleet-select" wire:model.live="locationId" aria-label="Location">
                    <option value="">All locations</option>
                    @foreach ($board['locations'] as $location)
                        <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                    @endforeach
                </select>
                <a class="ir-fleet-create" href="{{ $this->createBookingUrl() }}">New booking</a>
            </div>
        </div>

        <div class="ir-fleet-legend">
            <span><i class="ir-swatch ir-swatch-quote"></i> Stand by</span>
            <span><i class="ir-swatch ir-swatch-confirmed"></i> Confirmed</span>
            <span><i class="ir-swatch ir-swatch-active"></i> On the road</span>
            <span><i class="ir-swatch ir-swatch-returned"></i> Returned</span>
            <span><i class="ir-swatch ir-swatch-noshow"></i> No show</span>
            <span><i class="ir-swatch ir-swatch-block"></i> Blocked</span>
            <span>Select a booking to see the reservation.</span>
        </div>

        <div class="ir-fleet-board">
            <div style="width: {{ $board['sidebarWidth'] + $board['timelineWidth'] }}px">
                <div class="ir-fleet-head">
                    <div class="ir-fleet-side" style="width: {{ $board['sidebarWidth'] }}px">
                        <span class="ir-fleet-kicker">Vehicles</span>
                        <span class="ir-fleet-count">{{ $board['plateCount'] }} {{ $board['plateCount'] === 1 ? 'unit' : 'units' }}</span>
                    </div>
                    <div>
                        <div style="display:flex">
                            @foreach ($board['months'] as $month)
                                <div class="ir-month" style="width: {{ $month['count'] * $board['dayWidth'] }}px">{{ $month['label'] }}</div>
                            @endforeach
                        </div>
                        <div style="display:flex">
                            @foreach ($board['days'] as $day)
                                <div class="ir-day {{ $day['weekend'] ? 'is-weekend' : '' }} {{ $day['today'] ? 'is-today' : '' }}" style="width: {{ $board['dayWidth'] }}px">
                                    <span>{{ $day['number'] }}</span>
                                    <small>{{ $day['dow'] }}</small>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($board['groups'] === [])
                    <p class="ir-fleet-empty">No vehicles found for this location.</p>
                @endif

                @foreach ($board['groups'] as $group)
                    <div class="ir-fleet-group">
                        <div class="ir-fleet-side" style="width: {{ $board['sidebarWidth'] }}px">
                            <strong>{{ $group['label'] }}</strong>
                            <span class="ir-fleet-count">{{ $group['unitCount'] }} {{ $group['unitCount'] === 1 ? 'unit' : 'units' }}</span>
                        </div>
                        <div style="width: {{ $board['timelineWidth'] }}px"></div>
                    </div>
                    @foreach ($group['rows'] as $row)
                        <div class="ir-fleet-row {{ $row['active'] ? '' : 'is-inactive' }}">
                            <div class="ir-fleet-side" style="width: {{ $board['sidebarWidth'] }}px; height: {{ $row['height'] }}px">
                                <div class="ir-fleet-plate">
                                    <i class="ir-dot {{ $row['active'] ? '' : 'is-off' }}"></i>
                                    <div>
                                        <p class="title">{{ $row['title'] }}</p>
                                        <p class="sub">{{ $row['subtitle'] }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="ir-lane" style="width: {{ $board['timelineWidth'] }}px; height: {{ $row['height'] }}px">
                                <div class="ir-grid" aria-hidden="true">
                                    @foreach ($board['days'] as $day)
                                        <i class="{{ $day['weekend'] ? 'is-weekend' : '' }} {{ $day['today'] ? 'is-today' : '' }}" style="width: {{ $board['dayWidth'] }}px"></i>
                                    @endforeach
                                </div>
                                @foreach ($row['blocks'] as $block)
                                    <div class="ir-block" style="left: {{ $block['left'] }}px; width: {{ $block['width'] }}px; top: 6px; height: {{ max($row['height'] - 12, 30) }}px" title="{{ $block['label'] }}">{{ $block['label'] }}</div>
                                @endforeach
                                @foreach ($row['bars'] as $bar)
                                    <button type="button" class="ir-bar ir-bar-{{ $bar['tone'] }}" style="left: {{ $bar['left'] }}px; width: {{ $bar['width'] }}px; top: {{ $bar['top'] }}px" wire:click="selectOrder({{ $bar['id'] }})" title="{{ $bar['label'] }}">{{ $bar['label'] }}</button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    @if ($board['selected'])
        <button type="button" class="ir-dialog-back" wire:click="clearOrder" aria-label="Close details"></button>
        <div class="ir-dialog" role="dialog" aria-label="Reservation details">
            <h3>{{ $board['selected']['reference'] }}</h3>
            <p class="who">{{ $board['selected']['customer'] }}@if ($board['selected']['email']) · {{ $board['selected']['email'] }}@endif</p>
            <dl>
                <div class="line">
                    <dt>Status</dt>
                    <dd><span class="ir-pill ir-bar-{{ $board['selected']['tone'] }}">{{ $board['selected']['status'] }}</span></dd>
                </div>
                <div class="line">
                    <dt>Vehicle</dt>
                    <dd>{{ $board['selected']['vehicle'] }}</dd>
                </div>
                <div class="line">
                    <dt>Pickup</dt>
                    <dd>{{ $board['selected']['pickup'] }}@if ($board['selected']['pickupLocation'])<small>{{ $board['selected']['pickupLocation'] }}</small>@endif</dd>
                </div>
                <div class="line">
                    <dt>Return</dt>
                    <dd>{{ $board['selected']['dropoff'] }}@if ($board['selected']['dropoffLocation'])<small>{{ $board['selected']['dropoffLocation'] }}</small>@endif</dd>
                </div>
                @if ($board['selected']['phone'])
                    <div class="line">
                        <dt>Phone</dt>
                        <dd>{{ $board['selected']['phone'] }}</dd>
                    </div>
                @endif
                <div class="line total">
                    <dt>Total</dt>
                    <dd>{{ $board['selected']['total'] }}</dd>
                </div>
            </dl>
            <div class="ir-dialog-actions">
                <button type="button" class="ir-fleet-btn" wire:click="clearOrder">Close</button>
                <a class="ir-dialog-open" href="{{ $this->orderEditUrl($board['selected']['id']) }}">Open reservation</a>
            </div>
        </div>
    @endif
</x-filament-panels::page>
