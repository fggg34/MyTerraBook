@php
    use App\Filament\Resources\CustomFields\CustomFieldResource;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $typeLabel = static fn (mixed $type): string => $type === 'select' ? 'Dropdown' : ($type === 'text' ? 'Text' : \App\Support\AdminTableBadgeColors::humanize((string) $type));
@endphp

<div class="tb-list">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search field or label…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(120px,1fr) minmax(140px,1.2fr) 100px 90px 80px 70px 70px 40px">
            <span></span><span>Field name</span><span>Label</span><span>Type</span><span>Required</span><span>E-mail</span><span>Order</span><span>Active</span><span></span>
        </div>
        @forelse ($records as $record)
            @php $key = $getRecordKey($record); @endphp
            <div class="tb-row tb-grid" wire:key="field-{{ $key }}" style="grid-template-columns:20px minmax(120px,1fr) minmax(140px,1.2fr) 100px 90px 80px 70px 70px 40px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span>{{ $record->field_key }}</span>
                <span>{{ $record->label }}</span>
                <span>{{ $typeLabel($record->type) }}</span>
                <span>{{ $record->is_required ? 'Yes' : 'No' }}</span>
                <span>{{ $record->is_email ? 'Yes' : 'No' }}</span>
                <span>{{ $record->sort_order }}</span>
                <span>{{ $record->is_active ? 'Yes' : 'No' }}</span>
                <a class="tb-icon-btn" href="{{ CustomFieldResource::getUrl('edit', ['record' => $record]) }}" aria-label="Edit {{ $record->label }}">
                    <img src="{{ asset('admin-redesign/icon-tb-pencil.svg') }}" alt="" width="16" height="16">
                </a>
            </div>
        @empty
            <p class="tb-empty">No custom fields yet.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
