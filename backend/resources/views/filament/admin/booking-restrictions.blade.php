@php
    use App\Filament\Resources\BookingRestrictions\BookingRestrictionResource;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $empty = $records->isEmpty() && blank($this->tableSearch);
@endphp

<div class="tb-list">
    @if ($empty)
        <div class="tb-empty-card">
            <img src="{{ asset('admin-redesign/icon-tb-calendar.svg') }}" alt="" width="28" height="28">
            <strong>No booking restrictions yet</strong>
            <p>A restriction sets the shortest or longest rental for a range of dates.</p>
            @if (BookingRestrictionResource::canCreate())
                <a class="tb-btn tb-btn-md tb-btn-primary" href="{{ BookingRestrictionResource::getUrl('create') }}">
                    <img src="{{ asset('admin-redesign/icon-tb-plus-white.svg') }}" alt="" width="16" height="16">
                    New restriction
                </a>
            @endif
        </div>
    @else
        <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
            <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
            <div class="tb-toolbar">
                <label class="tb-search">
                    <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                    <input type="search" placeholder="Search restriction…" wire:model.live.debounce.300ms="tableSearch">
                </label>
                <div class="tb-toolbar-spacer"></div>
                <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                    <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
                </button>
            </div>
            <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(160px,1.4fr) minmax(160px,1.2fr) 80px 80px 90px 72px 56px">
                <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
                <span>Name</span><span>Dates</span><span>Min days</span><span>Max days</span><span>Multiplier</span><span>Active</span><span></span>
            </div>
            @forelse ($records as $record)
                @php $key = $getRecordKey($record); @endphp
                <div class="tb-row tb-grid" wire:key="restriction-{{ $key }}" style="grid-template-columns:20px minmax(160px,1.4fr) minmax(160px,1.2fr) 80px 80px 90px 72px 56px">
                    <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                    <span>{{ $record->name }}</span>
                    <span>{{ $record->date_from?->format('j M Y') }} – {{ $record->date_to?->format('j M Y') }}</span>
                    <span>{{ $record->min_rental_days ?: '—' }}</span>
                    <span>{{ $record->max_rental_days ?: '—' }}</span>
                    <span>{{ $record->min_length_multiplier ?: '—' }}</span>
                    <span><x-admin.switch :on="(bool) $record->is_active" :wire="'toggleActive('.$key.')'" label="Active" /></span>
                    <x-admin.row-menu :edit-url="BookingRestrictionResource::getUrl('edit', ['record' => $record])" :items="[]" />
                </div>
            @empty
                <p class="tb-empty">No booking restrictions match this search.</p>
            @endforelse
            <x-admin.table-footer :records="$records" :page-name="$pageName" />
        </div>
    @endif
</div>
<x-filament-actions::modals />
