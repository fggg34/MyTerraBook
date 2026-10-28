@php
    use App\Filament\Resources\Characteristics\CharacteristicResource;
    use App\Models\Characteristic;
    use App\Support\IconCatalog;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $groups = Characteristic::query()->whereNotNull('group')->where('group', '!=', '')->distinct()->orderBy('group')->pluck('group', 'group');
    $groupValue = data_get($this->tableFilters, 'group.value');
@endphp

<div class="tb-list" x-data="{ colsOpen: false, openFilter: null, cols: { order: false } }">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search feature…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($groupValue)]) x-on:click="openFilter = openFilter === 'group' ? null : 'group'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($groupValue) ? $groupValue : 'Group' }}
                </button>
                <select x-cloak x-show="openFilter === 'group'" wire:model.live="tableFilters.group.value">
                    <option value="">Any</option>
                    @foreach ($groups as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
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
                <menu x-cloak x-show="colsOpen"><li><label><input type="checkbox" x-model="cols.order"> Order</label></li></menu>
            </div>
        </div>
        <div class="tb-head tb-grid" :style="`grid-template-columns:20px 36px minmax(140px,1fr) minmax(140px,1fr) minmax(140px,1fr) ${cols.order ? '72px ' : ''}56px`">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Icon</span>
            <span>Feature name</span>
            <span>Text next to icon</span>
            <span>Group</span>
            <template x-if="cols.order"><span>Order</span></template>
            <span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $icon = IconCatalog::tableIconHtml($record->icon);
                $menu = [
                    ['label' => 'Move up', 'wire' => "mountTableAction('move_up', '{$key}')"],
                    ['label' => 'Move down', 'wire' => "mountTableAction('move_down', '{$key}')"],
                ];
            @endphp
            <div class="tb-row tb-grid" wire:key="feature-{{ $key }}" :style="`grid-template-columns:20px 36px minmax(140px,1fr) minmax(140px,1fr) minmax(140px,1fr) ${cols.order ? '72px ' : ''}56px`">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span class="tb-amenity-icon">{!! $icon !== '' ? $icon : '' !!}</span>
                <span>{{ $record->name }}</span>
                <span>{{ $record->display_text ?: '—' }}</span>
                <span>
                    @if (filled($record->group))
                        <x-admin.status-pill tone="info">{{ $record->group }}</x-admin.status-pill>
                    @elseif ($icon === '')
                        <span class="tb-warn-text">No group, no icon</span>
                    @else
                        —
                    @endif
                </span>
                <template x-if="cols.order"><span>{{ $record->sort_order }}</span></template>
                <x-admin.row-menu :edit-url="CharacteristicResource::getUrl('edit', ['record' => $record])" :items="$menu" />
            </div>
        @empty
            <p class="tb-empty">No features match these filters.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
