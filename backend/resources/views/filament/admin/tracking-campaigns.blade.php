@php
    use App\Filament\Resources\TrackingCampaigns\TrackingCampaignResource;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
@endphp

<div class="tb-list">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search campaign…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        @php $total = method_exists($records, 'total') ? $records->total() : $records->count(); @endphp
        @if ($total === 0 && blank($this->tableSearch))
            <div class="tb-empty-card">
                <strong>No tracking campaigns yet</strong>
                @if (TrackingCampaignResource::canCreate())
                    <a class="tb-btn tb-btn-md tb-btn-primary" href="{{ TrackingCampaignResource::getUrl('create') }}">New tracking campaign</a>
                @endif
            </div>
        @else
            <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(140px,1.2fr) minmax(120px,1fr) 120px 120px 140px 70px 40px">
                <span></span><span>Campaign</span><span>Link</span><span>UTM source</span><span>UTM medium</span><span>UTM campaign</span><span>Active</span><span></span>
            </div>
            @forelse ($records as $record)
                @php $key = $getRecordKey($record); @endphp
                <div class="tb-row tb-grid" wire:key="campaign-{{ $key }}" style="grid-template-columns:20px minmax(140px,1.2fr) minmax(120px,1fr) 120px 120px 140px 70px 40px">
                    <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                    <span>{{ $record->name }}</span>
                    <span>{{ $record->slug }}</span>
                    <span>{{ $record->utm_source ?: '—' }}</span>
                    <span>{{ $record->utm_medium ?: '—' }}</span>
                    <span>{{ $record->utm_campaign ?: '—' }}</span>
                    <span><x-admin.switch :on="(bool) $record->is_active" wire="toggleCampaign({{ $key }})" label="Active" /></span>
                    <a class="tb-icon-btn" href="{{ TrackingCampaignResource::getUrl('edit', ['record' => $record]) }}" aria-label="Edit {{ $record->name }}">
                        <img src="{{ asset('admin-redesign/icon-tb-pencil.svg') }}" alt="" width="16" height="16">
                    </a>
                </div>
            @empty
                <p class="tb-empty">No campaigns match this search.</p>
            @endforelse
            <x-admin.table-footer :records="$records" :page-name="$pageName" />
        @endif
    </div>
</div>
<x-filament-actions::modals />
