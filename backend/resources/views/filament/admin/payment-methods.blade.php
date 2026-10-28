@php
    use App\Filament\Resources\PaymentMethods\PaymentMethodResource;
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
                <input type="search" placeholder="Search code or name…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(120px,1fr) minmax(140px,1.2fr) 80px 110px 120px 110px 90px 70px 40px">
            <span></span><span>Code</span><span>Name</span><span>Enabled</span><span>Auto confirm</span><span>Charge or discount</span><span>Amount type</span><span>Amount</span><span>Order</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $amount = $record->charge_discount_type === 'fixed'
                    ? ($record->charge_fixed_cents === null ? '—' : AdminMoney::fromCents((int) $record->charge_fixed_cents))
                    : ($record->charge_percent_bips === null ? '—' : rtrim(rtrim(number_format(((int) $record->charge_percent_bips) / 100, 2, '.', ''), '0'), '.').'%');
            @endphp
            <div class="tb-row tb-grid" wire:key="pay-{{ $key }}" style="grid-template-columns:20px minmax(120px,1fr) minmax(140px,1.2fr) 80px 110px 120px 110px 90px 70px 40px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span>{{ $record->code }}</span>
                <span>{{ $record->name }}</span>
                <span>{{ $record->is_enabled ? 'Yes' : 'No' }}</span>
                <span>{{ $record->auto_confirm_order ? 'Yes' : 'No' }}</span>
                <span>{{ AdminTableBadgeColors::humanize((string) $record->charge_or_discount) }}</span>
                <span>{{ $record->charge_discount_type === 'fixed' ? 'Fixed amount' : AdminTableBadgeColors::humanize((string) $record->charge_discount_type) }}</span>
                <span>{{ $amount }}</span>
                <span>{{ $record->sort_order }}</span>
                <a class="tb-icon-btn" href="{{ PaymentMethodResource::getUrl('edit', ['record' => $record]) }}" aria-label="Edit {{ $record->name }}">
                    <img src="{{ asset('admin-redesign/icon-tb-pencil.svg') }}" alt="" width="16" height="16">
                </a>
            </div>
        @empty
            <p class="tb-empty">No payment methods yet.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
