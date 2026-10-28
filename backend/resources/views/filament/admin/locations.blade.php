@php
    use App\Filament\Resources\Locations\LocationResource;
    use App\Models\Location;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $destinations = Location::query()->whereNotNull('country_code')->where('country_code', '!=', '')->distinct()->orderBy('country_code')->pluck('country_code', 'country_code')
        ->map(fn ($code) => config('destinations.'.$code, $code));
    $destination = data_get($this->tableFilters, 'country_code.value');
@endphp

<div class="tb-list" x-data="{ colsOpen: false, openFilter: null, cols: { created: false, updated: false } }">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search location…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($destination)]) x-on:click="openFilter = openFilter === 'destination' ? null : 'destination'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($destination) ? ($destinations[$destination] ?? $destination) : 'Destination' }}
                </button>
                <select x-cloak x-show="openFilter === 'destination'" wire:model.live="tableFilters.country_code.value">
                    <option value="">Any</option>
                    @foreach ($destinations as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
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
        <div class="tb-head tb-grid" :style="`grid-template-columns:20px minmax(180px,1.4fr) 120px 120px 72px minmax(120px,1fr) ${cols.created ? '110px ' : ''}${cols.updated ? '110px ' : ''}56px`">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Location</span>
            <span>Destination</span>
            <span>Open</span>
            <span>Vehicles</span>
            <span>Tax override</span>
            <template x-if="cols.created"><span>Created</span></template>
            <template x-if="cols.updated"><span>Updated</span></template>
            <span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $opens = $record->default_opening_time ? \Illuminate\Support\Carbon::parse($record->default_opening_time)->format('H:i') : null;
                $closes = $record->default_closing_time ? \Illuminate\Support\Carbon::parse($record->default_closing_time)->format('H:i') : null;
            @endphp
            <div class="tb-row tb-grid" wire:key="location-{{ $key }}" :style="`grid-template-columns:20px minmax(180px,1.4fr) 120px 120px 72px minmax(120px,1fr) ${cols.created ? '110px ' : ''}${cols.updated ? '110px ' : ''}56px`">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span class="tb-stack">
                    <strong>{{ $record->name }}</strong>
                    <small @class(['tb-warn-text' => blank($record->address)])>{{ filled($record->address) ? $record->address : 'No address yet' }}</small>
                </span>
                <span>{{ config('destinations.'.$record->country_code, $record->country_code ?: '—') }}</span>
                <span>{{ $opens && $closes ? $opens.' – '.$closes : '—' }}</span>
                <span>{{ (int) ($record->cars_count ?? 0) }}</span>
                <span>{{ $record->taxRate?->name ?: '—' }}</span>
                <template x-if="cols.created"><span>{{ $record->created_at?->format('j M Y') }}</span></template>
                <template x-if="cols.updated"><span>{{ $record->updated_at?->format('j M Y') }}</span></template>
                <x-admin.row-menu :edit-url="LocationResource::getUrl('edit', ['record' => $record])" :items="[]" />
            </div>
        @empty
            <p class="tb-empty">No locations match these filters.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
