@php
    use App\Filament\Resources\HourlyFares\HourlyFareResource;
    use App\Support\AdminMoney;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $span = function (int $minutes): string {
        if ($minutes > 0 && $minutes % 60 === 0) {
            $hours = (int) ($minutes / 60);

            return $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        return $minutes.' min';
    };
    $empty = $records->isEmpty() && blank($this->tableSearch);
@endphp

<div class="tb-list">
    @if ($empty)
        <div class="tb-empty-card">
            <img src="{{ asset('admin-redesign/icon-tb-calendar.svg') }}" alt="" width="28" height="28">
            <strong>No hourly fares yet</strong>
            <p>A short rental is priced as one total for a range of minutes, for example 3 hours.</p>
            @if (HourlyFareResource::canCreate())
                <a class="tb-btn tb-btn-md tb-btn-primary" href="{{ HourlyFareResource::getUrl('create') }}">
                    <img src="{{ asset('admin-redesign/icon-tb-plus-white.svg') }}" alt="" width="16" height="16">
                    New hourly fare
                </a>
            @endif
        </div>
    @else
        <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
            <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
            <div class="tb-toolbar">
                <label class="tb-search">
                    <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                    <input type="search" placeholder="Search hourly fare…" wire:model.live.debounce.300ms="tableSearch">
                </label>
                <div class="tb-toolbar-spacer"></div>
                <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                    <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
                </button>
            </div>
            <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(160px,1.2fr) minmax(120px,1fr) 110px 110px 120px 56px">
                <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
                <span>Vehicle</span><span>Price type</span><span>From</span><span>To</span><span>Total price</span><span></span>
            </div>
            @forelse ($records as $record)
                @php $key = $getRecordKey($record); @endphp
                <div class="tb-row tb-grid" wire:key="hourly-{{ $key }}" style="grid-template-columns:20px minmax(160px,1.2fr) minmax(120px,1fr) 110px 110px 120px 56px">
                    <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                    <span>{{ $record->car?->name ?: '—' }}</span>
                    <span>{{ $record->priceType?->name ?: '—' }}</span>
                    <span>{{ $span((int) $record->min_minutes) }}</span>
                    <span>{{ $span((int) $record->max_minutes) }}</span>
                    <span>{{ AdminMoney::fromCents((int) $record->total_price_cents) }}</span>
                    <x-admin.row-menu :edit-url="HourlyFareResource::getUrl('edit', ['record' => $record])" :items="[]" />
                </div>
            @empty
                <p class="tb-empty">No hourly fares match this search.</p>
            @endforelse
            <x-admin.table-footer :records="$records" :page-name="$pageName" />
        </div>
    @endif
</div>
<x-filament-actions::modals />
