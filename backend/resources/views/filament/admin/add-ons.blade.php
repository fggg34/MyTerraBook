@php
    use App\Filament\Resources\RentalOptions\RentalOptionResource;
    use App\Models\RentalOption;
    use App\Support\AdminMoney;
    use App\Support\IconCatalog;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $allFree = RentalOption::query()->exists() && RentalOption::query()->where('cost_cents', '>', 0)->doesntExist();
@endphp

<div class="tb-list">
    @if ($allFree)
        <div class="tb-banner">Every add-on is priced at ISK 0. Customers can still add them at checkout.</div>
    @endif
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search add-on…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px 20px 36px minmax(160px,1.4fr) 110px 110px 110px 56px 88px 56px">
            <span></span>
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span></span><span>Add-on</span><span>Price</span><span>Charged</span><span>Max cost</span><span>Qty</span><span>Always on</span><span></span>
        </div>
        <div x-sortable x-on:end.stop="$wire.reorderVisible($event.target.sortable.toArray())">
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $icon = IconCatalog::tableIconHtml($record->icon);
                $menu = [
                    ['label' => 'Move up', 'wire' => "mountTableAction('move_up', '{$key}')"],
                    ['label' => 'Move down', 'wire' => "mountTableAction('move_down', '{$key}')"],
                ];
                $free = (int) $record->cost_cents === 0;
            @endphp
            <div class="tb-row tb-grid" wire:key="addon-{{ $key }}" x-sortable-item="{{ $key }}" style="grid-template-columns:20px 20px 36px minmax(160px,1.4fr) 110px 110px 110px 56px 88px 56px">
                <button type="button" class="tb-grip" x-sortable-handle aria-label="Drag to reorder"></button>
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span class="tb-amenity-icon">{!! $icon !== '' ? $icon : '' !!}</span>
                <span>{{ $record->name }}</span>
                <span @class(['tb-warn-text' => $free])>{{ AdminMoney::fromCents((int) $record->cost_cents) }}</span>
                <span>{{ $record->is_daily_cost ? 'Per day' : 'Per rental' }}</span>
                <span>{{ $record->max_cost_cap_cents ? AdminMoney::fromCents((int) $record->max_cost_cap_cents) : '—' }}</span>
                <span>{{ $record->has_quantity ? '✓' : '—' }}</span>
                <span><x-admin.switch :on="(bool) $record->is_mandatory" :wire="'toggleMandatory('.$key.')'" label="Always on" /></span>
                <x-admin.row-menu :edit-url="RentalOptionResource::getUrl('edit', ['record' => $record])" :items="$menu" />
            </div>
        @empty
            <p class="tb-empty">No add-ons match this search.</p>
        @endforelse
        </div>
        <x-admin.table-footer :records="$records" :page-name="$pageName" :per-page="$this->getTableRecordsPerPage()" />
    </div>
</div>
<x-filament-actions::modals />
