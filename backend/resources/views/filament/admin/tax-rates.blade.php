@php
    use App\Filament\Resources\PriceTypes\PriceTypeResource;
    use App\Filament\Resources\TaxRates\TaxRateResource;
    use App\Models\PriceType;
    use App\Models\TaxRate;

    $records = $getRecords();
    $search = trim((string) ($this->tableSearch ?? ''));
    $rateLabel = function (TaxRate $rate): string {
        $percent = ((int) $rate->basis_points) / 100;

        return rtrim(rtrim(number_format($percent, 2, '.', ''), '0'), '.').'%';
    };
    $usedBy = function (TaxRate $rate): string {
        $parts = [];
        if ($rate->price_types_count) {
            $parts[] = 'Price types';
        }
        if ($rate->rental_options_count) {
            $parts[] = 'Add-ons';
        }
        if ($rate->location_fees_count) {
            $parts[] = 'Location fees';
        }
        if ($rate->out_of_hours_fees_count) {
            $parts[] = 'Out-of-hours fees';
        }

        return $parts !== [] ? implode(', ', $parts) : '—';
    };
    $types = PriceType::query()
        ->with('taxRate')
        ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
        ->orderBy('name')
        ->get();
@endphp

<div class="tb-list">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search tax rate or price type…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected tax rates" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <section>
            <h2 class="tb-card-title">Tax rates</h2>
            <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(160px,1.2fr) 80px minmax(180px,1.4fr) 56px">
                <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select tax rates" x-on:click="toggleSelectRecordsOnPage"></span>
                <span>Name</span><span>Rate</span><span>Used by</span><span></span>
            </div>
            @forelse ($records as $rate)
                @php $key = (string) $rate->getKey(); @endphp
                <div class="tb-row tb-grid" wire:key="tax-{{ $key }}" style="grid-template-columns:20px minmax(160px,1.2fr) 80px minmax(180px,1.4fr) 56px">
                    <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                    <span>{{ $rate->name }}</span>
                    <span>{{ $rateLabel($rate) }}</span>
                    <span>{{ $usedBy($rate) }}</span>
                    <x-admin.row-menu :edit-url="TaxRateResource::getUrl('edit', ['record' => $rate])" :items="[]" />
                </div>
            @empty
                <p class="tb-empty">No tax rates match this search.</p>
            @endforelse
        </section>
        <section id="types-of-price">
            <h2 class="tb-card-title">Types of price</h2>
            <div class="tb-head tb-grid" style="grid-template-columns:minmax(160px,1.2fr) minmax(160px,1.2fr) minmax(140px,1fr) 56px">
                <span>Price type</span><span>Attributes</span><span>Tax rate</span><span></span>
            </div>
            @forelse ($types as $type)
                <div class="tb-row tb-grid" wire:key="price-type-{{ $type->getKey() }}" style="grid-template-columns:minmax(160px,1.2fr) minmax(160px,1.2fr) minmax(140px,1fr) 56px">
                    <span>{{ $type->name }}</span>
                    <span>{{ $type->attribute_label ?: '—' }}</span>
                    <span>
                        @if ($type->taxRate)
                            {{ $type->taxRate->name }}
                        @else
                            <span class="tb-warn-text">No tax rate</span>
                        @endif
                    </span>
                    <x-admin.row-menu :edit-url="PriceTypeResource::getUrl('edit', ['record' => $type])" :items="[]" />
                </div>
            @empty
                <p class="tb-empty">No price types match this search.</p>
            @endforelse
        </section>
    </div>
</div>
<x-filament-actions::modals />
