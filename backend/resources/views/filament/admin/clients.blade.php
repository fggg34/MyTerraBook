@php
    use App\Filament\Resources\RegisteredClients\RegisteredClientResource;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
@endphp

<div class="tb-list">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search name, e-mail, phone…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(180px,1.6fr) 140px 110px 70px 120px 72px">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Client</span><span>Phone</span><span>Car bookings</span><span>Stays</span><span>Registered</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $words = preg_split('/\s+/', trim((string) $record->name)) ?: [];
                $initials = strtoupper(mb_substr((string) ($words[0] ?? ''), 0, 1).mb_substr((string) ($words[1] ?? ''), 0, 1));
                $menu = [
                    ['label' => 'Delete', 'wire' => "mountTableAction('delete', '{$key}')", 'danger' => true],
                ];
            @endphp
            <div class="tb-row tb-grid" wire:key="client-{{ $key }}" style="grid-template-columns:20px minmax(180px,1.6fr) 140px 110px 70px 120px 72px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <x-admin.entity-cell :title="$record->name" :subtitle="$record->email" :initials="$initials ?: '—'" round />
                <span>{{ $record->phone ?: '—' }}</span>
                <span>{{ (int) $record->orders_count }}</span>
                <span>{{ (int) $record->guest_house_bookings_count }}</span>
                <span>{{ $record->created_at?->format('j M Y') ?: '—' }}</span>
                <x-admin.row-menu icon="icon-tb-eye.svg" :edit-url="RegisteredClientResource::getUrl('view', ['record' => $record])" :items="$menu" />
            </div>
        @empty
            <p class="tb-empty">No clients match this search.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
