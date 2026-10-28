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
        <p class="tb-empty" style="border-bottom:1px solid #e3e8ef">Sort order is the stored order. Popup link URL stays on the edit form. Both fields appear on the reservation page as “Custom field”.</p>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(140px,1.2fr) minmax(120px,1fr) 100px 90px 90px 80px 40px">
            <span></span><span>Field</span><span>Key</span><span>Type</span><span>Required</span><span>E-mail field</span><span>Active</span><span></span>
        </div>
        @forelse ($records as $record)
            @php $key = $getRecordKey($record); @endphp
            <div class="tb-row tb-grid" wire:key="field-{{ $key }}" style="grid-template-columns:20px minmax(140px,1.2fr) minmax(120px,1fr) 100px 90px 90px 80px 40px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span class="tb-stack"><strong>{{ $record->label }}</strong><small>Shown on the booking form</small></span>
                <span>{{ $record->field_key }}</span>
                <span>{{ $typeLabel($record->type) }}</span>
                <span><x-admin.switch :on="(bool) $record->is_required" :wire="'toggleCustomField('.$key.', \'is_required\')'" label="Required" /></span>
                <span><x-admin.switch :on="(bool) $record->is_email" :wire="'toggleCustomField('.$key.', \'is_email\')'" label="E-mail field" /></span>
                <span><x-admin.switch :on="(bool) $record->is_active" :wire="'toggleCustomField('.$key.', \'is_active\')'" label="Active" /></span>
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
