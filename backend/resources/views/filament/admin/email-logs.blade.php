@php
    use App\Support\AdminTableBadgeColors;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $statusValue = data_get($this->tableFilters, 'status.value');
    $statuses = ['queued' => 'Queued', 'sent' => 'Sent', 'failed' => 'Failed'];
@endphp

<div class="tb-list" x-data="{ openFilter: null }">
    <div class="tb-table-card">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search recipient or subject…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($statusValue)]) x-on:click="openFilter = openFilter === 'status' ? null : 'status'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ $statuses[$statusValue] ?? 'Status' }}
                </button>
                <select x-cloak x-show="openFilter === 'status'" wire:model.live="tableFilters.status.value">
                    <option value="">Any</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:150px 160px minmax(160px,1fr) minmax(180px,1.4fr) 100px">
            <span>Sent</span><span>Template</span><span>Recipient</span><span>Subject</span><span>Status</span>
        </div>
        @forelse ($records as $record)
            @php
                $status = (string) $record->status;
                $when = $record->sent_at ?? $record->created_at;
            @endphp
            <div class="tb-row tb-grid" wire:key="log-{{ $record->getKey() }}" style="grid-template-columns:150px 160px minmax(160px,1fr) minmax(180px,1.4fr) 100px">
                <span>{{ $when?->format('j M Y, H:i') ?: '—' }}</span>
                <span>@if ($record->template_key)<x-admin.status-pill tone="info">{{ AdminTableBadgeColors::humanize((string) $record->template_key) }}</x-admin.status-pill>@else — @endif</span>
                <span>{{ $record->recipient ?: '—' }}</span>
                <span>{{ $record->subject ?: '—' }}</span>
                <span><x-admin.status-pill :tone="$status === 'sent' ? 'ok' : ($status === 'failed' ? 'bad' : 'muted')">{{ AdminTableBadgeColors::humanize($status) }}</x-admin.status-pill></span>
            </div>
        @empty
            <p class="tb-empty">No e-mails have been logged yet.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
