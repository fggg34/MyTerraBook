@php
    use App\Filament\Resources\ExtraHourFares\ExtraHourFareResource;
    use App\Support\AdminMoney;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $empty = $records->isEmpty() && blank($this->tableSearch);
    $extraMode = (string) data_get(\App\Models\Setting::getValue('shop.apply_extra_hours_charges', ['mode' => 'before_special_prices']), 'mode', 'before_special_prices');
    $extraWhen = $extraMode === 'after_special_prices' ? 'after pricing rules' : 'before pricing rules';
@endphp

<div class="tb-list">
    @if ($empty)
        <div class="tb-empty-card">
            <img src="{{ asset('admin-redesign/icon-tb-calendar.svg') }}" alt="" width="28" height="28">
            <strong>No extra hour fares yet</strong>
            <p>This is the charge for each hour a vehicle comes back after the booked return time. Apply extra hours charges is set to {{ $extraWhen }}.</p>
            @if (ExtraHourFareResource::canCreate())
                <a class="tb-btn tb-btn-md tb-btn-primary" href="{{ ExtraHourFareResource::getUrl('create') }}">
                    <img src="{{ asset('admin-redesign/icon-tb-plus-white.svg') }}" alt="" width="16" height="16">
                    New extra hour fare
                </a>
            @endif
        </div>
    @else
        <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
            <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
            <div class="tb-toolbar">
                <label class="tb-search">
                    <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                    <input type="search" placeholder="Search extra hour fare…" wire:model.live.debounce.300ms="tableSearch">
                </label>
                <div class="tb-toolbar-spacer"></div>
                <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                    <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
                </button>
            </div>
            <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(180px,1.4fr) minmax(140px,1fr) 140px 56px">
                <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
                <span>Vehicle</span><span>Price type</span><span>Per extra hour</span><span></span>
            </div>
            @forelse ($records as $record)
                @php $key = $getRecordKey($record); @endphp
                <div class="tb-row tb-grid" wire:key="extra-hour-{{ $key }}" style="grid-template-columns:20px minmax(180px,1.4fr) minmax(140px,1fr) 140px 56px">
                    <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                    <span>{{ $record->car?->name ?: '—' }}</span>
                    <span>{{ $record->priceType?->name ?: '—' }}</span>
                    <span>{{ AdminMoney::fromCents((int) $record->charge_per_extra_hour_cents) }}</span>
                    <x-admin.row-menu :edit-url="ExtraHourFareResource::getUrl('edit', ['record' => $record])" :items="[]" />
                </div>
            @empty
                <p class="tb-empty">No extra hour fares match this search.</p>
            @endforelse
            <x-admin.table-footer :records="$records" :page-name="$pageName" />
        </div>
    @endif
</div>
<x-filament-actions::modals />
