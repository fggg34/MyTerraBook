@php
    use App\Filament\Resources\CarUnits\CarUnitResource;
    use App\Models\Car;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $cars = Car::query()->orderBy('name')->pluck('name', 'id');
    $carValue = data_get($this->tableFilters, 'car_id.value');
@endphp

<div class="tb-list" x-data="{ colsOpen: false, openFilter: null, cols: { created: false, updated: false } }">
    <div
        class="tb-table-card"
        x-data="filamentTable({
            areGroupsCollapsedByDefault: false,
            canTrackDeselectedRecords: false,
            currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()),
            maxSelectableRecords: null,
            selectsCurrentPageOnly: true,
            $wire,
        })"
    >
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search unit…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($carValue)]) x-on:click="openFilter = openFilter === 'car' ? null : 'car'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($carValue) ? ($cars[$carValue] ?? 'Vehicle') : 'Vehicle' }}
                </button>
                <select x-cloak x-show="openFilter === 'car'" wire:model.live="tableFilters.car_id.value">
                    <option value="">Any</option>
                    @foreach ($cars as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-btn tb-btn-sm" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
                Bulk actions
            </button>
            <div class="tb-columns">
                <button type="button" class="tb-icon-btn" aria-label="Columns" x-on:click="colsOpen = !colsOpen">
                    <img src="{{ asset('admin-redesign/icon-tb-columns.svg') }}" alt="" width="16" height="16">
                </button>
                <menu x-cloak x-show="colsOpen">
                    <li><label><input type="checkbox" x-model="cols.created"> Created</label></li>
                    <li><label><input type="checkbox" x-model="cols.updated"> Updated</label></li>
                </menu>
            </div>
        </div>
        <div class="tb-head tb-grid" :style="`grid-template-columns:20px minmax(180px,1fr) 72px 72px ${cols.created ? '110px ' : ''}${cols.updated ? '110px ' : ''}40px`">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Vehicle unit</span>
            <span>Order</span>
            <span>Active</span>
            <template x-if="cols.created"><span>Created</span></template>
            <template x-if="cols.updated"><span>Updated</span></template>
            <span></span>
        </div>
        @forelse ($records as $record)
            @php $key = $getRecordKey($record); @endphp
            <div class="tb-row tb-grid" wire:key="unit-{{ $key }}" :style="`grid-template-columns:20px minmax(180px,1fr) 72px 72px ${cols.created ? '110px ' : ''}${cols.updated ? '110px ' : ''}40px`">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <x-admin.entity-cell :title="$record->car?->name ?: 'Unit '.$record->getKey()" :image="asset('admin-redesign/icon-tb-car.svg')" round glyph />
                <span>{{ $record->sort_order }}</span>
                <span><x-admin.switch :on="(bool) $record->is_active" :wire="'toggleActive('.$key.')'" label="Active" /></span>
                <template x-if="cols.created"><span>{{ $record->created_at?->format('j M Y') }}</span></template>
                <template x-if="cols.updated"><span>{{ $record->updated_at?->format('j M Y') }}</span></template>
                <a class="tb-icon-btn" href="{{ CarUnitResource::getUrl('edit', ['record' => $record]) }}" aria-label="Edit">
                    <img src="{{ asset('admin-redesign/icon-tb-pencil.svg') }}" alt="" width="16" height="16">
                </a>
            </div>
        @empty
            <p class="tb-empty">No fleet units match these filters.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
