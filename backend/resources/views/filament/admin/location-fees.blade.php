@php
    use App\Filament\Resources\LocationFees\LocationFeeResource;
    use App\Support\AdminMoney;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $mark = fn (bool $on): string => $on ? '✓' : '—';
    $rateLabel = function ($rate): string {
        if (! $rate) {
            return '—';
        }
        if (str_contains((string) $rate->name, '%')) {
            return $rate->name;
        }
        $percent = rtrim(rtrim(number_format(((int) $rate->basis_points) / 100, 2, '.', ''), '0'), '.');

        return $rate->name.' ('.$percent.'%)';
    };
@endphp

<div class="tb-list">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search route…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(180px,1.6fr) 110px 72px 80px 80px minmax(120px,1fr) 72px 56px">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Route</span><span>Fee</span><span>Per day</span><span>One way</span><span>Inverted</span><span>Tax</span><span>Active</span><span></span>
        </div>
        @forelse ($records as $record)
            @php $key = $getRecordKey($record); @endphp
            <div class="tb-row tb-grid" wire:key="location-fee-{{ $key }}" style="grid-template-columns:20px minmax(180px,1.6fr) 110px 72px 80px 80px minmax(120px,1fr) 72px 56px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span>{{ $record->pickupLocation?->name ?: '—' }} → {{ $record->dropoffLocation?->name ?: '—' }}</span>
                <span>{{ AdminMoney::fromCents((int) $record->cost_cents) }}</span>
                <span>{{ $mark((bool) $record->multiply_by_days) }}</span>
                <span>{{ $mark((bool) $record->is_one_way_fee) }}</span>
                <span>{{ $mark((bool) $record->apply_inverted) }}</span>
                <span>{{ $rateLabel($record->taxRate) }}</span>
                <span><x-admin.switch :on="(bool) $record->is_active" :wire="'toggleActive('.$key.')'" label="Active" /></span>
                <x-admin.row-menu :edit-url="LocationFeeResource::getUrl('edit', ['record' => $record])" :items="[]" />
            </div>
        @empty
            <p class="tb-empty">No location fees match this search.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
