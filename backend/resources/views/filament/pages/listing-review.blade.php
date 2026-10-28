<x-filament-panels::page>
    <style>
        .la-shell {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .la-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .la-stat {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #fff;
            padding: 1rem 1.125rem;
        }

        .dark .la-stat {
            border-color: #334155;
            background: #0f172a;
        }

        .la-stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
        }

        .la-stat-value {
            margin-top: 0.35rem;
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1;
            color: #0f172a;
        }

        .dark .la-stat-value {
            color: #f8fafc;
        }

        .la-stat-value--warn {
            color: var(--mtb-primary-dark);
        }

        .la-panel {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            background: #fff;
            overflow: hidden;
        }

        .dark .la-panel {
            border-color: #334155;
            background: #0f172a;
        }

        .la-toolbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }

        .dark .la-toolbar {
            border-color: #334155;
            background: #111827;
        }

        .la-tabs {
            display: inline-flex;
            gap: 0.35rem;
            padding: 0.2rem;
            border-radius: 0.65rem;
            background: #e2e8f0;
        }

        .dark .la-tabs {
            background: #1e293b;
        }

        .la-tab {
            border: 0;
            border-radius: 0.5rem;
            padding: 0.45rem 0.85rem;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #475569;
            background: transparent;
            cursor: pointer;
            transition: background-color 120ms ease, color 120ms ease;
        }

        .la-tab:hover {
            color: #0f172a;
        }

        .dark .la-tab {
            color: #94a3b8;
        }

        .dark .la-tab:hover {
            color: #f8fafc;
        }

        .la-tab--active {
            background: #fff;
            color: #0f172a;
            box-shadow: 0 1px 2px rgb(15 23 42 / 0.08);
        }

        .dark .la-tab--active {
            background: #334155;
            color: #f8fafc;
        }

        .la-tab-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            margin-left: 0.35rem;
            padding: 0 0.35rem;
            border-radius: 999px;
            font-size: 0.6875rem;
            font-weight: 700;
            background: #fef3c7;
            color: #92400e;
        }

        .la-toolbar-note {
            font-size: 0.8125rem;
            color: #64748b;
        }

        .la-table-wrap {
            overflow-x: auto;
        }

        .la-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .la-table thead th {
            padding: 0.7rem 1rem;
            text-align: left;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .dark .la-table thead th {
            color: #94a3b8;
            background: #111827;
            border-color: #334155;
        }

        .la-table tbody td {
            padding: 0.9rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .dark .la-table tbody td {
            border-color: #1e293b;
            color: #cbd5e1;
        }

        .la-table tbody tr:hover {
            background: #f8fafc;
        }

        .dark .la-table tbody tr:hover {
            background: #111827;
        }

        .la-listing {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 14rem;
        }

        .la-thumb {
            width: 3rem;
            height: 3rem;
            border-radius: 0.5rem;
            object-fit: cover;
            background: #e2e8f0;
            flex-shrink: 0;
        }

        .la-thumb--empty {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.625rem;
            font-weight: 700;
            color: #64748b;
        }

        .la-listing-name {
            font-weight: 600;
            color: #0f172a;
        }

        .dark .la-listing-name {
            color: #f8fafc;
        }

        .la-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.2rem 0.55rem;
            font-size: 0.6875rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .la-badge--purple {
            background: #f3e8ff;
            color: #7e22ce;
        }

        .la-badge--blue {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .dark .la-badge--purple {
            background: rgb(126 34 206 / 0.2);
            color: #d8b4fe;
        }

        .dark .la-badge--blue {
            background: rgb(29 78 216 / 0.2);
            color: #93c5fd;
        }

        .la-host-name {
            font-weight: 600;
            color: #0f172a;
        }

        .dark .la-host-name {
            color: #f8fafc;
        }

        .la-muted {
            font-size: 0.8125rem;
            color: #64748b;
        }

        .la-details {
            font-size: 0.8125rem;
            line-height: 1.45;
            max-width: 16rem;
        }

        .la-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            min-width: 11rem;
        }

        .la-btn {
            border: 1px solid transparent;
            border-radius: 0.45rem;
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
        }

        .la-btn--approve {
            background: #16a34a;
            color: #fff;
        }

        .la-btn--changes {
            background: #fff7ed;
            border-color: #fdba74;
            color: #c2410c;
        }

        .la-btn--reject {
            background: #fef2f2;
            border-color: #fca5a5;
            color: #b91c1c;
        }

        .la-btn--edit {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #334155;
        }

        .dark .la-btn--changes {
            background: rgb(194 65 12 / 0.15);
            border-color: rgb(251 146 60 / 0.35);
            color: #fdba74;
        }

        .dark .la-btn--reject {
            background: rgb(185 28 28 / 0.15);
            border-color: rgb(248 113 113 / 0.35);
            color: #fca5a5;
        }

        .dark .la-btn--edit {
            background: #1e293b;
            border-color: #475569;
            color: #e2e8f0;
        }

        .la-empty {
            padding: 2.5rem 1.5rem;
            text-align: center;
        }

        .la-empty-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }

        .dark .la-empty-title {
            color: #f8fafc;
        }

        .la-empty-text {
            margin-top: 0.35rem;
            font-size: 0.875rem;
            color: #64748b;
        }

        @media (max-width: 900px) {
            .la-stats {
                grid-template-columns: 1fr;
            }
        }

        html.tb-redesign .la-stat,
        html.tb-redesign .la-panel {
            border-color: #dbe3ec;
            border-radius: 12px;
        }
        html.tb-redesign .la-stat-label {
            font-family: "IBM Plex Sans", sans-serif;
            letter-spacing: 0;
            color: #5c6b7a;
            text-transform: none;
            font-size: 13px;
        }
        html.tb-redesign .la-table thead th {
            font-family: "IBM Plex Mono", ui-monospace, monospace;
            letter-spacing: 0.8px;
            color: #8a99a9;
            text-transform: uppercase;
        }
        html.tb-redesign .la-stat-value,
        html.tb-redesign .la-listing-name,
        html.tb-redesign .la-host-name,
        html.tb-redesign .la-empty-title {
            color: #0d1b2a;
        }
        html.tb-redesign .la-stat-value--warn { color: #9a5b00; }
        html.tb-redesign .la-toolbar {
            background: #f3f6fa;
            border-color: #dbe3ec;
        }
        html.tb-redesign .la-tabs { background: #e8eff7; }
        html.tb-redesign .la-tab { color: #5b6b7d; }
        html.tb-redesign .la-tab--active { background: #fff; color: #041e37; }
        html.tb-redesign .la-tab-badge { background: #fdf1dc; color: #9a5b00; }
        html.tb-redesign .la-table thead th { background: #f3f6fa; border-color: #dbe3ec; }
        html.tb-redesign .la-table tbody td { border-color: #dbe3ec; color: #0d1b2a; }
        html.tb-redesign .la-muted,
        html.tb-redesign .la-toolbar-note,
        html.tb-redesign .la-empty-text { color: #5b6b7d; }
        html.tb-redesign .la-toolbar-note { display: none; }
        html.tb-redesign .la-btn--approve { background: #041e37; }
        html.tb-redesign .la-btn--changes { background: #fff; border-color: #d5deea; color: #041e37; }
        html.tb-redesign .la-btn--reject { background: #fde7e5; border-color: #f7c4c0; color: #b42318; }
        html.tb-redesign .la-btn--edit { background: #fff; border-color: #dbe3ec; color: #0d1b2a; }
        html.tb-redesign .la-badge--purple { background: #e8eff7; color: #4474a4; }
        html.tb-redesign .la-badge--blue { background: #e3f3ea; color: #1f7a4d; }
        html.tb-redesign .la-cards {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 16px;
        }
        html.tb-redesign .la-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border: 1px solid #dbe3ec;
            border-radius: 12px;
            background: #fff;
        }
        html.tb-redesign .la-card .la-thumb {
            width: 56px;
            height: 56px;
            border-radius: 8px;
        }
        html.tb-redesign .la-card-main { flex: 1; min-width: 0; }
        html.tb-redesign .la-card-title {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }
        html.tb-redesign .la-badge--wait { background: #fdf1dc; color: #9a5b00; }
        html.tb-redesign .la-empty {
            padding: 3rem 1.5rem;
        }
        html.tb-redesign .la-empty-icon {
            width: 40px;
            height: 40px;
            margin: 0 auto 12px;
            border-radius: 999px;
            background: #e3f3ea;
            color: #1f7a4d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
    </style>

    <div class="la-shell">
        <div class="la-stats">
            <div class="la-stat">
                <div class="la-stat-label">Total pending</div>
                <div class="la-stat-value {{ $this->pendingTotalCount > 0 ? 'la-stat-value--warn' : '' }}">
                    {{ $this->pendingTotalCount }}
                </div>
            </div>
            <div class="la-stat">
                <div class="la-stat-label">Guest houses</div>
                <div class="la-stat-value">{{ $this->pendingGuestHouseCount }}</div>
            </div>
            <div class="la-stat">
                <div class="la-stat-label">Vehicles</div>
                <div class="la-stat-value">{{ $this->pendingVehicleCount }}</div>
            </div>
        </div>

        <div class="la-panel">
            <div class="la-toolbar">
                <div class="la-tabs" role="tablist" aria-label="Approval queue filters">
                    <button
                        type="button"
                        role="tab"
                        class="la-tab {{ $activeTab === 'all' ? 'la-tab--active' : '' }}"
                        wire:click="setActiveTab('all')"
                    >
                        All
                        @if ($this->pendingTotalCount > 0)
                            <span class="la-tab-badge">{{ $this->pendingTotalCount }}</span>
                        @endif
                    </button>
                    <button
                        type="button"
                        role="tab"
                        class="la-tab {{ $activeTab === 'guesthouses' ? 'la-tab--active' : '' }}"
                        wire:click="setActiveTab('guesthouses')"
                    >
                        Guest houses
                        <span class="la-tab-badge">{{ $this->pendingGuestHouseCount }}</span>
                    </button>
                    <button
                        type="button"
                        role="tab"
                        class="la-tab {{ $activeTab === 'vehicles' ? 'la-tab--active' : '' }}"
                        wire:click="setActiveTab('vehicles')"
                    >
                        Vehicles
                        <span class="la-tab-badge">{{ $this->pendingVehicleCount }}</span>
                    </button>
                </div>
                <p class="la-toolbar-note">
                    Newest submissions first · approve, request changes, or reject
                </p>
            </div>

            @if ($this->queueItems->isEmpty())
                <div class="la-empty">
                    <div class="la-empty-icon" aria-hidden="true">✓</div>
                    <div class="la-empty-title">Queue is clear</div>
                    <p class="la-empty-text">
                        No host listings are waiting for review. New submissions appear here with photos, price and a side-by-side view of what changed.
                    </p>
                </div>
            @else
                <div class="la-cards">
                    @foreach ($this->queueItems as $item)
                        <article class="la-card" wire:key="{{ $item['key'] }}">
                            @if ($item['image_url'])
                                <img src="{{ $item['image_url'] }}" alt="" class="la-thumb" loading="lazy" />
                            @else
                                <span class="la-thumb la-thumb--empty">No img</span>
                            @endif
                            <div class="la-card-main">
                                <div class="la-card-title">
                                    <span class="la-listing-name">{{ $item['name'] }}</span>
                                    <span class="la-badge la-badge--{{ $item['type_color'] }}">{{ $item['type_label'] }}</span>
                                    <span class="la-badge la-badge--wait">Waiting</span>
                                </div>
                                <div class="la-muted">
                                    Submitted by {{ $item['host_name'] }}
                                    @if ($item['submitted_at'])
                                        · {{ $item['submitted_at']->format('j M Y, H:i') }}
                                    @endif
                                    @if ($item['details'])
                                        · {{ $item['details'] }}
                                    @endif
                                </div>
                            </div>
                            <div class="la-actions">
                                <a href="{{ $item['edit_url'] }}" class="la-btn la-btn--edit">Preview</a>
                                @if ($item['entity'] === 'guesthouse')
                                    <button type="button" class="la-btn la-btn--changes" wire:click="mountAction('requestGuestHouseChanges', { guestHouseId: {{ $item['entity_id'] }} })">Request changes</button>
                                    <button type="button" class="la-btn la-btn--reject" wire:click="mountAction('rejectGuestHouse', { guestHouseId: {{ $item['entity_id'] }} })">Reject</button>
                                    <button type="button" class="la-btn la-btn--approve" wire:click="approveGuestHouse({{ $item['entity_id'] }})">Approve</button>
                                @else
                                    <button type="button" class="la-btn la-btn--changes" wire:click="mountAction('requestCarChanges', { carId: {{ $item['entity_id'] }} })">Request changes</button>
                                    <button type="button" class="la-btn la-btn--reject" wire:click="mountAction('rejectCar', { carId: {{ $item['entity_id'] }} })">Reject</button>
                                    <button type="button" class="la-btn la-btn--approve" wire:click="approveCar({{ $item['entity_id'] }})">Approve</button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
