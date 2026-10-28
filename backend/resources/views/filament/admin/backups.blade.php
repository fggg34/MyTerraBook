@php
    use App\Filament\Resources\Backups\BackupResource;
    use App\Support\AdminTableBadgeColors;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
@endphp

<div class="tb-list">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search file or path…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px 90px minmax(160px,1.4fr) minmax(140px,1fr) 90px 100px 40px">
            <span></span><span>Disk</span><span>Path</span><span>File</span><span>Size</span><span>Type</span><span></span>
        </div>
        @forelse ($records as $record)
            @php $key = $getRecordKey($record); @endphp
            <div class="tb-row tb-grid" wire:key="backup-{{ $key }}" style="grid-template-columns:20px 90px minmax(160px,1.4fr) minmax(140px,1fr) 90px 100px 40px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span>{{ $record->disk }}</span>
                <span>{{ $record->path }}</span>
                <span>{{ $record->filename }}</span>
                <span>{{ $record->size_bytes === null ? '—' : number_format((int) $record->size_bytes) }}</span>
                <span>{{ AdminTableBadgeColors::humanize((string) $record->backup_type) }}</span>
                <a class="tb-icon-btn" href="{{ BackupResource::getUrl('edit', ['record' => $record]) }}" aria-label="Edit {{ $record->filename }}">
                    <img src="{{ asset('admin-redesign/icon-tb-pencil.svg') }}" alt="" width="16" height="16">
                </a>
            </div>
        @empty
            <p class="tb-empty">No backups yet.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
