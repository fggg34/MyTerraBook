@php
    use App\Filament\Resources\Categories\CategoryResource;
    use App\Filament\Resources\SubCategories\SubCategoryResource;
    use App\Models\MainCategory;
    use App\Models\SubCategory;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $mains = MainCategory::query()->orderBy('sort_order')->orderBy('name')->get();
    $trash = data_get($this->tableFilters, 'trashed.value');
    $subs = SubCategory::query()
        ->with('mainCategory')
        ->when($trash === true, fn ($query) => $query->withTrashed())
        ->when($trash === false, fn ($query) => $query->onlyTrashed())
        ->when(filled($this->mainCategory), fn ($query) => $query->where('main_category_id', $this->mainCategory))
        ->orderBy('sort_order')
        ->paginate(10, ['*'], 'subs')
        ->withQueryString();
@endphp

<div class="tb-list" x-data="{ colsOpen: false, cols: { order: false, slug: false } }">
    <div class="tb-block">
        <h2>Main categories</h2>
        <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
            <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
            <div class="tb-toolbar">
                <label class="tb-search">
                    <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                    <input type="search" placeholder="Search category…" wire:model.live.debounce.300ms="tableSearch">
                </label>
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
                <div class="tb-columns">
                    <button type="button" class="tb-icon-btn" aria-label="Columns" x-on:click="colsOpen = !colsOpen">
                        <img src="{{ asset('admin-redesign/icon-tb-columns.svg') }}" alt="" width="16" height="16">
                    </button>
                    <menu x-cloak x-show="colsOpen">
                        <li><label><input type="checkbox" x-model="cols.order"> Order</label></li>
                        <li><label><input type="checkbox" x-model="cols.slug"> Slug</label></li>
                    </menu>
                </div>
            </div>
            <div class="tb-head tb-grid" :style="`grid-template-columns:20px minmax(160px,1fr) minmax(180px,1.4fr) 110px ${cols.order ? '72px ' : ''}${cols.slug ? '120px ' : ''}56px`">
                <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
                <span>Main category</span>
                <span>Description</span>
                <span>Sub categories</span>
                <template x-if="cols.order"><span>Order</span></template>
                <template x-if="cols.slug"><span>Slug</span></template>
                <span></span>
            </div>
            @forelse ($records as $record)
                @php
                    $key = $getRecordKey($record);
                    $mainMenu = [
                        ['label' => 'Move up', 'wire' => "mountTableAction('move_up', '{$key}')"],
                        ['label' => 'Move down', 'wire' => "mountTableAction('move_down', '{$key}')"],
                    ];
                @endphp
                <div class="tb-row tb-grid" wire:key="main-{{ $key }}" :style="`grid-template-columns:20px minmax(160px,1fr) minmax(180px,1.4fr) 110px ${cols.order ? '72px ' : ''}${cols.slug ? '120px ' : ''}56px`">
                    <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                    <x-admin.entity-cell :title="$record->name" :image="asset($record->slug === 'campervan' ? 'admin-redesign/icon-tb-fleet.svg' : 'admin-redesign/icon-tb-car.svg')" glyph />
                    <span>{{ $record->description ?: '—' }}</span>
                    <span>{{ (int) ($record->sub_categories_count ?? 0) }}</span>
                    <template x-if="cols.order"><span>{{ $record->sort_order }}</span></template>
                    <template x-if="cols.slug"><span>{{ $record->slug }}</span></template>
                    <x-admin.row-menu :edit-url="CategoryResource::getUrl('edit', ['record' => $record])" :items="$mainMenu" />
                </div>
            @empty
                <p class="tb-empty">No main categories match these filters.</p>
            @endforelse
            <x-admin.table-footer :records="$records" :page-name="$pageName" />
        </div>
    </div>

    <div class="tb-block">
        <div class="tb-toolbar tb-toolbar-plain">
            <h2>Sub categories</h2>
            <div class="tb-seg tb-seg-inline">
                <button type="button" @class(['is-active' => blank($this->mainCategory)]) wire:click="$set('mainCategory', null)">All</button>
                @foreach ($mains as $main)
                    <button type="button" @class(['is-active' => (string) $this->mainCategory === (string) $main->getKey()]) wire:click="$set('mainCategory', '{{ $main->getKey() }}')">{{ $main->name }}</button>
                @endforeach
            </div>
        </div>
        <div class="tb-table-card">
            <div class="tb-head tb-grid" style="grid-template-columns:minmax(160px,1fr) minmax(120px,1fr) minmax(140px,1.2fr) 120px 56px">
                <span>Sub category</span>
                <span>Main category</span>
                <span>Description</span>
                <span>Show in search filter</span>
                <span></span>
            </div>
            @forelse ($subs as $sub)
                @php
                    $subMenu = [
                        ['label' => 'Move up', 'wire' => 'moveSub('.$sub->getKey().', \'up\')'],
                        ['label' => 'Move down', 'wire' => 'moveSub('.$sub->getKey().', \'down\')'],
                    ];
                @endphp
                <div class="tb-row tb-grid" wire:key="sub-{{ $sub->getKey() }}" style="grid-template-columns:minmax(160px,1fr) minmax(120px,1fr) minmax(140px,1.2fr) 120px 56px">
                    <span>{{ $sub->name }}</span>
                    <span>{{ $sub->mainCategory?->name ?: '—' }}</span>
                    <span>{{ $sub->description ?: '—' }}</span>
                    <span><x-admin.switch :on="(bool) $sub->is_search_filter" :wire="'toggleSearchFilter('.$sub->getKey().')'" label="Show in search filter" /></span>
                    <x-admin.row-menu :edit-url="SubCategoryResource::getUrl('edit', ['record' => $sub])" :items="$subMenu" />
                </div>
            @empty
                <p class="tb-empty">No sub categories in this group.</p>
            @endforelse
            <x-admin.table-footer :records="$subs" page-name="subs" />
        </div>
    </div>
</div>
<x-filament-actions::modals />
