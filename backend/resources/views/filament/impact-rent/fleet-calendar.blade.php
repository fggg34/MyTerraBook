<x-filament-panels::page>
    @php
        $board = $this->board();
        $sections = $board['sections'] ?? [];
    @endphp

    <style>
        .ir-fleet { display: flex; flex-direction: column; gap: 12px; }
        .ir-fleet-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .ir-fleet-nav, .ir-fleet-tools { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .ir-fleet-nav h2 { margin: 0 0 0 6px; font-size: 16px; font-weight: 700; color: #0d1b2a; }
        .ir-fleet-btn, .ir-fleet-select {
            height: 36px;
            border: 1px solid #dbe3ec;
            border-radius: 8px;
            background: #fff;
            color: #0d1b2a;
            font-size: 13px;
            font-weight: 600;
        }
        .ir-fleet-btn { padding: 0 12px; cursor: pointer; }
        .ir-fleet-btn:hover, .ir-fleet-btn.is-on { background: #e8eff7; }
        .ir-fleet-select { min-width: 10rem; padding: 0 10px; }
        .ir-fleet-seg { display: inline-flex; padding: 3px; border: 1px solid #dbe3ec; border-radius: 10px; background: #fff; }
        .ir-fleet-seg button {
            height: 30px;
            padding: 0 10px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: #5b6b7d;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }
        .ir-fleet-seg button.is-on { background: #e8eff7; color: #041e37; }
        .ir-fleet-create {
            display: inline-flex;
            align-items: center;
            height: 38px;
            padding: 0 16px;
            border-radius: 6px;
            background: #041e37;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }
        .ir-fleet-legend { display: flex; flex-wrap: wrap; gap: 12px; color: #5b6b7d; font-size: 12px; }
        .ir-fleet-legend button, .ir-fleet-legend span { display: inline-flex; align-items: center; gap: 6px; border: 0; background: transparent; color: inherit; cursor: pointer; font: inherit; }
        .ir-swatch { width: 12px; height: 12px; border-radius: 3px; border: 1px solid transparent; }
        .ir-swatch-quote { background: #e8eff7; border-color: #c5d6e8; }
        .ir-swatch-confirmed { background: #0b2c4d; }
        .ir-swatch-active { background: #fdf1dc; border-color: #f0d5a0; }
        .ir-swatch-returned { background: #e3f3ea; border-color: #b7decc; }
        .ir-swatch-noshow { background: #fde7e5; border-color: #f4b4ae; }
        .ir-swatch-block {
            border-color: #94a3b8;
            background: repeating-linear-gradient(-45deg, #e2e8f0, #e2e8f0 4px, #cbd5e1 4px, #cbd5e1 8px);
        }
        .ir-fleet-board {
            overflow: auto;
            max-height: 72vh;
            border: 1px solid #dbe3ec;
            border-radius: 12px;
            background: #fff;
        }
        .ir-fleet-head, .ir-fleet-group, .ir-fleet-row { display: flex; }
        .ir-fleet-head { position: sticky; top: 0; z-index: 30; border-bottom: 1px solid #dbe3ec; background: #f7f9fc; }
        .ir-fleet-side {
            position: sticky;
            left: 0;
            z-index: 20;
            flex: 0 0 auto;
            box-sizing: border-box;
            border-right: 1px solid #dbe3ec;
            background: #fff;
        }
        .ir-fleet-head .ir-fleet-side { z-index: 40; background: #f7f9fc; display: flex; flex-direction: column; justify-content: center; padding: 8px 12px; }
        .ir-fleet-kicker { font-family: "IBM Plex Mono", ui-monospace, monospace; font-size: 10.5px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: #8a99a9; }
        .ir-fleet-count { font-size: 11px; color: #8a99a9; }
        .ir-month {
            overflow: hidden;
            border-right: 1px solid #dbe3ec;
            background: #e8eff7;
            color: #0b2c4d;
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
            border-right: 1px solid #eaf0f5;
            padding: 0.2rem 0;
            font-size: 11px;
            font-variant-numeric: tabular-nums;
            color: #5b6b7d;
        }
        .ir-day small { font-size: 9px; text-transform: uppercase; color: #8a99a9; }
        .ir-day.is-weekend { background: rgba(232, 239, 247, 0.45); }
        .ir-day.is-today { background: #041e37; color: #fff; font-weight: 600; }
        .ir-day.is-today small { color: #dbe5f2; }
        .ir-fleet-group { border-bottom: 1px solid #eaf0f5; background: #f7f9fc; }
        .ir-fleet-group .ir-fleet-side { background: #f7f9fc; padding: 6px 12px; }
        .ir-fleet-group strong { font-size: 11px; letter-spacing: 1px; color: #8a99a9; }
        .ir-fleet-row { border-bottom: 1px solid #eaf0f5; }
        .ir-fleet-row.is-inactive { opacity: 0.6; }
        .ir-fleet-plate { display: flex; align-items: center; gap: 8px; height: 100%; padding: 0 12px; }
        .ir-dot { width: 8px; height: 8px; border-radius: 999px; background: #4474a4; flex: 0 0 auto; }
        .ir-dot.is-off { background: #dbe3ec; }
        .ir-fleet-plate p { margin: 0; }
        .ir-fleet-plate .title { font-size: 13px; font-weight: 600; color: #0d1b2a; }
        .ir-fleet-plate .sub { font-size: 11px; color: #8a99a9; }
        .ir-lane { position: relative; }
        .ir-grid { position: absolute; inset: 0; display: flex; }
        .ir-grid i { display: block; height: 100%; border-right: 1px solid #f3f6fa; box-sizing: border-box; }
        .ir-grid i.is-weekend { background: #f7f9fc; }
        .ir-grid i.is-today { background: #e8eff7; }
        .ir-bar, .ir-block {
            position: absolute;
            display: flex;
            align-items: center;
            overflow: hidden;
            border-radius: 6px;
            border: 1px solid transparent;
            padding: 0 6px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }
        .ir-bar { height: 30px; cursor: pointer; z-index: 2; }
        .ir-bar-quote { background: #e8eff7; border-color: #c5d6e8; color: #4474a4; }
        .ir-bar-confirmed { background: #0b2c4d; border-color: #0b2c4d; color: #fff; }
        .ir-bar-active { background: #fdf1dc; border-color: #f0d5a0; color: #9a5b00; }
        .ir-bar-returned { background: #e3f3ea; border-color: #b7dech; color: #1f7a4d; }
        .ir-bar-noshow { background: #fde7e5; border-color: #f4b4ae; color: #b42318; }
        .ir-block {
            z-index: 1;
            color: #5b6b7d;
            border-color: rgba(148, 163, 184, 0.7);
            background: repeating-linear-gradient(-45deg, #e2e8f0, #e2e8f0 4px, #cbd5e1 4px, #cbd5e1 8px);
        }
        .ir-fleet-empty { margin: 0; padding: 3rem 1rem; text-align: center; color: #8a99a9; font-size: 0.875rem; }
        .ir-dialog-back {
            position: fixed;
            inset: 0;
            z-index: 70;
            background: rgba(4, 30, 55, 0.35);
        }
        .ir-dialog {
            position: fixed;
            z-index: 80;
            top: 50%;
            left: 50%;
            width: min(28rem, calc(100vw - 2rem));
            transform: translate(-50%, -50%);
            border-radius: 12px;
            background: #fff;
            padding: 1.25rem;
            box-shadow: 0 20px 40px rgba(4, 30, 55, 0.18);
        }
        .ir-dialog h3 { margin: 0; font-size: 1.05rem; color: #0d1b2a; }
        .ir-dialog .who { margin: 0.25rem 0 0; color: #5b6b7d; font-size: 0.875rem; }
        .ir-dialog dl { margin: 1rem 0 0; display: grid; gap: 0.75rem; }
        .ir-dialog .line { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.875rem; }
        .ir-dialog dt { color: #8a99a9; }
        .ir-dialog dd { margin: 0; text-align: right; font-weight: 600; color: #0d1b2a; }
        .ir-dialog dd small { display: block; font-weight: 400; color: #8a99a9; }
        .ir-dialog .total { border-top: 1px solid #dbe3ec; padding-top: 0.75rem; }
        .ir-dialog-actions { display: flex; justify-content: space-between; gap: 0.5rem; margin-top: 1.25rem; }
        .ir-dialog-open {
            display: inline-flex;
            align-items: center;
            height: 2.25rem;
            padding: 0 0.9rem;
            border-radius: 6px;
            background: #041e37;
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
                <button type="button" class="ir-fleet-btn" wire:click="shiftSpan(-1)" aria-label="Earlier">←</button>
                <button type="button" class="ir-fleet-btn" wire:click="jumpToday">Today</button>
                <button type="button" class="ir-fleet-btn" wire:click="shiftSpan(1)" aria-label="Later">→</button>
                <h2>{{ $board['rangeLabel'] }}</h2>
            </div>
            <div class="ir-fleet-tools">
                <div class="ir-fleet-seg" role="tablist" aria-label="Range">
                    <button type="button" class="{{ $this->span === 'month' ? 'is-on' : '' }}" wire:click="setSpan('month')">Month</button>
                    <button type="button" class="{{ $this->span === 'week' ? 'is-on' : '' }}" wire:click="setSpan('week')">Week</button>
                    <button type="button" class="{{ $this->span === 'day' ? 'is-on' : '' }}" wire:click="setSpan('day')">Day</button>
                </div>
            </div>
        </div>

        <div class="ir-fleet-toolbar">
            <div class="ir-fleet-seg" role="tablist" aria-label="Catalog">
                <button type="button" class="{{ $this->catalog === 'all' ? 'is-on' : '' }}" wire:click="setCatalog('all')">All</button>
                <button type="button" class="{{ $this->catalog === 'cars' ? 'is-on' : '' }}" wire:click="setCatalog('cars')">Cars</button>
                <button type="button" class="{{ $this->catalog === 'stays' ? 'is-on' : '' }}" wire:click="setCatalog('stays')">Guest houses</button>
            </div>
            <div class="ir-fleet-tools">
                <select class="ir-fleet-select" wire:model.live="locationId" aria-label="Location">
                    <option value="">All locations</option>
                    @foreach ($board['locations'] as $location)
                        <option value="{{ $location['id'] }}">{{ $location['name'] }}</option>
                    @endforeach
                </select>
                <select class="ir-fleet-select" wire:model.live="statusFilter" aria-label="Status">
                    <option value="">All statuses</option>
                    <option value="quote">Stand by</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="active">On the road</option>
                    <option value="returned">Returned</option>
                    <option value="noshow">No show</option>
                </select>
                @if (($board['hosts'] ?? []) !== [])
                    <select class="ir-fleet-select" wire:model.live="hostId" aria-label="Host">
                        <option value="">Host</option>
                        @foreach ($board['hosts'] as $host)
                            <option value="{{ $host['id'] }}">{{ $host['name'] }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <div class="ir-fleet-legend">
            <span><i class="ir-swatch ir-swatch-quote"></i> Stand by</span>
            <span><i class="ir-swatch ir-swatch-confirmed"></i> Confirmed</span>
            <span><i class="ir-swatch ir-swatch-active"></i> On the road</span>
            <span><i class="ir-swatch ir-swatch-returned"></i> Returned</span>
            <span><i class="ir-swatch ir-swatch-noshow"></i> No show</span>
            <span><i class="ir-swatch ir-swatch-block"></i> Blocked</span>
        </div>

        <div class="ir-fleet-board">
            <div style="width: {{ $board['sidebarWidth'] + $board['timelineWidth'] }}px">
                <div class="ir-fleet-head">
                    <div class="ir-fleet-side" style="width: {{ $board['sidebarWidth'] }}px">
                        <span class="ir-fleet-kicker">Bookings</span>
                        <span class="ir-fleet-count">{{ $board['plateCount'] }} vehicles · {{ $board['stayCount'] ?? 0 }} guest houses</span>
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
                                    <small>{{ $day['dow'] }}</small>
                                    <span>{{ $day['number'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if ($sections === [])
                    <p class="ir-fleet-empty">No vehicles or guest houses match these filters.</p>
                @endif

                @foreach ($sections as $section)
                    <div class="ir-fleet-group">
                        <div class="ir-fleet-side" style="width: {{ $board['sidebarWidth'] }}px">
                            <strong>{{ $section['label'] }}</strong>
                        </div>
                        <div style="width: {{ $board['timelineWidth'] }}px"></div>
                    </div>
                    @foreach ($section['rows'] as $row)
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
                                    <button
                                        type="button"
                                        class="ir-bar ir-bar-{{ $bar['tone'] }}"
                                        style="left: {{ $bar['left'] }}px; width: {{ $bar['width'] }}px; top: {{ $bar['top'] }}px"
                                        @if (($bar['kind'] ?? 'order') === 'stay')
                                            wire:click="selectStay({{ $bar['id'] }})"
                                        @else
                                            wire:click="selectOrder({{ $bar['id'] }})"
                                        @endif
                                        title="{{ $bar['title'] ?? $bar['label'] }}"
                                    >{{ $bar['label'] }}</button>
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
                    <dt>{{ ($board['selectedKind'] ?? 'order') === 'stay' ? 'Property' : 'Vehicle' }}</dt>
                    <dd>{{ $board['selected']['vehicle'] }}</dd>
                </div>
                <div class="line">
                    <dt>{{ ($board['selectedKind'] ?? 'order') === 'stay' ? 'Check-in' : 'Pickup' }}</dt>
                    <dd>{{ $board['selected']['pickup'] }}@if ($board['selected']['pickupLocation'])<small>{{ $board['selected']['pickupLocation'] }}</small>@endif</dd>
                </div>
                <div class="line">
                    <dt>{{ ($board['selectedKind'] ?? 'order') === 'stay' ? 'Check-out' : 'Return' }}</dt>
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
                @if (($board['selectedKind'] ?? 'order') === 'stay')
                    <a class="ir-dialog-open" href="{{ $this->stayEditUrl($board['selected']['id']) }}">Open reservation</a>
                @else
                    <a class="ir-dialog-open" href="{{ $this->orderEditUrl($board['selected']['id']) }}">Open reservation</a>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
