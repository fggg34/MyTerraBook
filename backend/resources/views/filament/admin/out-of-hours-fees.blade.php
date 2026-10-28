@php
    use App\Filament\Resources\OutOfHoursFees\OutOfHoursFeeResource;
    use App\Support\AdminMoney;
    use App\Support\AdminTableBadgeColors;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $clock = function ($value): string {
        if (blank($value)) {
            return '—';
        }
        try {
            return \Illuminate\Support\Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return (string) $value;
        }
    };
    $rateLabel = function ($rate): string {
        if (! $rate) {
            return '';
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
                <input type="search" placeholder="Search fee…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(160px,1.3fr) 130px 100px 100px 100px 90px 72px 56px">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Fee</span><span>Hours</span><span>Applies to</span><span>Pickup</span><span>Drop-off</span><span>Max</span><span>Active</span><span></span>
        </div>
        @forelse ($records as $record)
            @php $key = $getRecordKey($record); @endphp
            <div class="tb-row tb-grid" wire:key="ooh-{{ $key }}" style="grid-template-columns:20px minmax(160px,1.3fr) 130px 100px 100px 100px 90px 72px 56px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span class="tb-stack">
                    <strong>{{ $record->name }}</strong>
                    @if ($record->taxRate)
                        <small>{{ $rateLabel($record->taxRate) }}</small>
                    @endif
                </span>
                <span>{{ $clock($record->time_from) }} → {{ $clock($record->time_to) }}</span>
                <span><x-admin.status-pill tone="info">{{ AdminTableBadgeColors::humanize((string) $record->applies_to) }}</x-admin.status-pill></span>
                <span>{{ AdminMoney::fromCents((int) $record->pickup_cost_cents) }}</span>
                <span>{{ AdminMoney::fromCents((int) $record->dropoff_cost_cents) }}</span>
                <span>{{ AdminMoney::fromCents((int) $record->max_combined_charge_cents) }}</span>
                <span><x-admin.switch :on="(bool) $record->is_active" :wire="'toggleActive('.$key.')'" label="Active" /></span>
                <x-admin.row-menu :edit-url="OutOfHoursFeeResource::getUrl('edit', ['record' => $record])" :items="[]" />
            </div>
        @empty
            <p class="tb-empty">No out-of-hours fees match this search.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
