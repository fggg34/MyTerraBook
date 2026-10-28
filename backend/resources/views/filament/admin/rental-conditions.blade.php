@php
    use App\Filament\Resources\RentalConditions\RentalConditionResource;
    use App\Support\IconCatalog;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
@endphp

<div class="tb-list" x-data="{ colsOpen: false, cols: { order: false } }">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search condition…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-btn tb-btn-sm" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
                Bulk actions
            </button>
            <div class="tb-columns">
                <button type="button" class="tb-icon-btn" aria-label="Columns" x-on:click="colsOpen = !colsOpen">
                    <img src="{{ asset('admin-redesign/icon-tb-columns.svg') }}" alt="" width="16" height="16">
                </button>
                <menu x-cloak x-show="colsOpen"><li><label><input type="checkbox" x-model="cols.order"> Order</label></li></menu>
            </div>
        </div>
        <div class="tb-head tb-grid" :style="`grid-template-columns:20px 36px minmax(160px,1fr) minmax(180px,1.4fr) 72px ${cols.order ? '72px ' : ''}56px`">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Icon</span>
            <span>Condition</span>
            <span>Description</span>
            <span>Active</span>
            <template x-if="cols.order"><span>Order</span></template>
            <span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $menu = [
                    ['label' => 'Move up', 'wire' => "mountTableAction('move_up', '{$key}')"],
                    ['label' => 'Move down', 'wire' => "mountTableAction('move_down', '{$key}')"],
                ];
            @endphp
            <div class="tb-row tb-grid" wire:key="condition-{{ $key }}" :style="`grid-template-columns:20px 36px minmax(160px,1fr) minmax(180px,1.4fr) 72px ${cols.order ? '72px ' : ''}56px`">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span class="tb-amenity-icon">{!! IconCatalog::tableIconHtml($record->icon) !!}</span>
                <span class="tb-stack">
                    <strong>{{ $record->title ?: $record->name }}</strong>
                    @if (filled($record->title) && filled($record->name) && $record->title !== $record->name)
                        <small>Admin name: {{ $record->name }}</small>
                    @endif
                </span>
                <span>{{ $record->description ?: '—' }}</span>
                <span><x-admin.switch :on="(bool) $record->is_active" :wire="'toggleActive('.$key.')'" label="Active" /></span>
                <template x-if="cols.order"><span>{{ $record->sort_order }}</span></template>
                <x-admin.row-menu :edit-url="RentalConditionResource::getUrl('edit', ['record' => $record])" :items="$menu" />
            </div>
        @empty
            <p class="tb-empty">No rental conditions match these filters.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
