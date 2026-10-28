@php
    use App\Filament\Resources\SubCategories\SubCategoryResource;
    use App\Models\MainCategory;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $mains = MainCategory::query()->orderBy('name')->pluck('name', 'id');
    $mainValue = data_get($this->tableFilters, 'main_category_id.value');
    $trash = data_get($this->tableFilters, 'trashed.value');
@endphp

<div class="tb-list" x-data="{ openFilter: null }">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search sub category…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($mainValue)]) x-on:click="openFilter = openFilter === 'main' ? null : 'main'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($mainValue) ? ($mains[$mainValue] ?? 'Main category') : 'Main category' }}
                </button>
                <select x-cloak x-show="openFilter === 'main'" wire:model.live="tableFilters.main_category_id.value">
                    <option value="">Any</option>
                    @foreach ($mains as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tb-toolbar-spacer"></div>
            <select class="tb-chip" aria-label="Deleted records" wire:change="setTrashView($event.target.value)">
                <option value="active" @selected($trash === null)>Active</option>
                <option value="with" @selected($trash === true)>With deleted</option>
                <option value="only" @selected($trash === false)>Only deleted</option>
            </select>
            <details class="tb-menu">
                <summary class="tb-btn tb-btn-sm">
                    <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
                    Bulk actions
                </summary>
                <menu>
                    <li><button type="button" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">Delete</button></li>
                    <li><button type="button" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('forceDelete')">Delete forever</button></li>
                    <li><button type="button" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('restore')">Restore</button></li>
                </menu>
            </details>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(160px,1fr) minmax(120px,1fr) minmax(140px,1.2fr) 140px 56px">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Sub category</span>
            <span>Main category</span>
            <span>Description</span>
            <span>Show in search filter</span>
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
            <div class="tb-row tb-grid" wire:key="subpage-{{ $key }}" style="grid-template-columns:20px minmax(160px,1fr) minmax(120px,1fr) minmax(140px,1.2fr) 140px 56px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span>{{ $record->name }}</span>
                <span>{{ $record->mainCategory?->name ?: '—' }}</span>
                <span>{{ $record->description ?: '—' }}</span>
                <span><x-admin.switch :on="(bool) $record->is_search_filter" :wire="'toggleSearchFilter('.$key.')'" label="Show in search filter" /></span>
                <x-admin.row-menu :edit-url="SubCategoryResource::getUrl('edit', ['record' => $record])" :items="$menu" />
            </div>
        @empty
            <p class="tb-empty">No sub categories match these filters.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
