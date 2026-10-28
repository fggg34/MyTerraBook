@php
    use App\Filament\Resources\Coupons\CouponResource;
    use App\Support\AdminMoney;
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
                <input type="search" placeholder="Search code…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(120px,1fr) 110px 120px minmax(160px,1.2fr) 110px 100px 56px">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Code</span><span>Type</span><span>Discount</span><span>Valid</span><span>Min. order</span><span>Status</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $percentMode = str_contains(strtolower((string) $record->discount_type), 'percent')
                    || ((int) $record->discount_percent_bips > 0 && (int) $record->discount_fixed_cents === 0);
                $discount = $percentMode
                    ? rtrim(rtrim(number_format(((int) $record->discount_percent_bips) / 100, 2, '.', ''), '0'), '.').'%'
                    : AdminMoney::fromCents((int) $record->discount_fixed_cents);
                $expired = $record->valid_to && $record->valid_to->endOfDay()->isPast();
                $status = $expired ? 'Expired' : ($record->is_active ? 'Active' : 'Inactive');
                $tone = $status === 'Active' ? 'ok' : ($status === 'Expired' ? 'bad' : 'neutral');
                $from = $record->valid_from?->format('j M Y');
                $to = $record->valid_to?->format('j M Y');
            @endphp
            <div class="tb-row tb-grid" wire:key="coupon-{{ $key }}" style="grid-template-columns:20px minmax(120px,1fr) 110px 120px minmax(160px,1.2fr) 110px 100px 56px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span class="tb-mono">{{ $record->code }}</span>
                <span><x-admin.status-pill tone="info">{{ AdminTableBadgeColors::humanize((string) $record->type) }}</x-admin.status-pill></span>
                <span>{{ $discount }}</span>
                <span>{{ $from || $to ? trim(($from ?: '—').' – '.($to ?: '—')) : '—' }}</span>
                <span>{{ $record->min_order_total_cents ? AdminMoney::fromCents((int) $record->min_order_total_cents) : '—' }}</span>
                <span><x-admin.status-pill :tone="$tone">{{ $status }}</x-admin.status-pill></span>
                <x-admin.row-menu :edit-url="CouponResource::getUrl('edit', ['record' => $record])" :items="[]" />
            </div>
        @empty
            <p class="tb-empty">No discount codes match this search.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
