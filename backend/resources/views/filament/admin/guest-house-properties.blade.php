@php
    use App\Enums\GuestHouseStatus;
    use App\Enums\GuestHouseType;
    use App\Filament\GuestHouse\Resources\GuestHouseResource;
    use App\Models\GuestHouse;
    use App\Models\User;
    use Illuminate\Support\Facades\Storage;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $types = collect(GuestHouseType::cases())->mapWithKeys(fn (GuestHouseType $type): array => [$type->value => ucfirst($type->value)]);
    $cities = GuestHouse::query()->whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city', 'city');
    $hosts = User::query()
        ->whereIn('id', GuestHouse::query()->whereNotNull('user_id')->distinct()->pluck('user_id'))
        ->orderBy('name')
        ->pluck('name', 'id');
    $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');
    $typeValue = data_get($this->tableFilters, 'type.value');
    $cityValue = data_get($this->tableFilters, 'city.value');
    $hostValue = data_get($this->tableFilters, 'user_id.value');
@endphp

<div class="tb-list" x-data="{ colsOpen: false, openFilter: null, cols: { type: true, capacity: true, price: true, host: true, status: true, updated: true } }">
    <x-admin.status-tabs :tabs="$this->getTabs()" :active="$this->activeTab ?? 'all'" />

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
            <input type="search" placeholder="Search name or city…" wire:model.live.debounce.300ms="tableSearch">
        </label>

        @foreach ([
            'type' => ['Type', $types, $typeValue],
            'city' => ['City', $cities, $cityValue],
            'user_id' => ['Host', $hosts, $hostValue],
        ] as $filter => [$label, $options, $current])
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($current)]) x-on:click="openFilter = openFilter === '{{ $filter }}' ? null : '{{ $filter }}'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($current) ? ($options[$current] ?? $label) : $label }}
                </button>
                <select
                    x-cloak
                    x-show="openFilter === '{{ $filter }}'"
                    wire:model.live="tableFilters.{{ $filter }}.value"
                >
                    <option value="">Any</option>
                    @foreach ($options as $value => $optionLabel)
                        <option value="{{ $value }}" @selected((string) $current === (string) $value)>{{ $optionLabel }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach

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
                @foreach (['type' => 'Type', 'capacity' => 'Capacity', 'price' => 'Price / night', 'host' => 'Host', 'status' => 'Status', 'updated' => 'Updated'] as $key => $label)
                    <li><label><input type="checkbox" x-model="cols.{{ $key }}"> {{ $label }}</label></li>
                @endforeach
            </menu>
        </div>
    </div>

    <div class="tb-head tb-grid">
            <span class="tb-keep">
                <input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage">
            </span>
            <span class="tb-keep">Property</span>
            <span :style="cols.type ? '' : 'visibility:hidden'">Type</span>
            <span :style="cols.capacity ? '' : 'visibility:hidden'">Capacity</span>
            <span class="tb-end" :style="cols.price ? '' : 'visibility:hidden'">Price / night</span>
            <span :style="cols.host ? '' : 'visibility:hidden'">Host</span>
            <span :style="cols.status ? '' : 'visibility:hidden'">Status</span>
            <span :style="cols.updated ? '' : 'visibility:hidden'">Updated</span>
            <span class="tb-keep"></span>
        </div>

    @forelse ($records as $record)
        @php
            $key = $getRecordKey($record);
            $thumb = $record->thumbnail;
            $thumbPath = is_array($thumb) ? ($thumb[0] ?? null) : $thumb;
            $feature = $record->amenities->first()?->name;
            $subline = collect([$record->city, $feature, $record->slug ? '/guesthouses/'.$record->slug : null])->filter(fn ($part) => filled($part))->implode(' · ');
            $nights = max(1, (int) $record->min_nights);
            $status = $record->status instanceof GuestHouseStatus ? $record->status->value : (string) $record->status;
            $tone = match ($status) {
                'active' => 'ok',
                'pending_review' => 'warn',
                default => 'bad',
            };
            $bedrooms = (int) $record->bedrooms;
            $bathrooms = (int) $record->bathrooms;
        @endphp
        <div class="tb-row tb-grid" wire:key="gh-{{ $key }}">
            <span class="tb-keep">
                <input
                    class="tb-check fi-ta-record-checkbox"
                    type="checkbox"
                    value="{{ $key }}"
                    x-on:click="toggleSelectedRecord(@js($key))"
                    x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"
                >
            </span>
            <x-admin.entity-cell
                :title="$record->name"
                :subtitle="$subline"
                :image="filled($thumbPath) ? Storage::disk('public')->url($thumbPath) : null"
            />
            <span :style="cols.type ? '' : 'visibility:hidden'"><x-admin.status-pill tone="info">{{ $types[$record->type?->value ?? $record->type] ?? ucfirst((string) ($record->type?->value ?? $record->type)) }}</x-admin.status-pill></span>
            <span class="tb-stack" :style="cols.capacity ? '' : 'visibility:hidden'">
                <strong>{{ (int) $record->max_guests }} guest{{ (int) $record->max_guests === 1 ? '' : 's' }}</strong>
                <small>{{ $bedrooms }} bedroom{{ $bedrooms === 1 ? '' : 's' }} · {{ $bathrooms }} bath{{ $bathrooms === 1 ? '' : 's' }}</small>
            </span>
            <span class="tb-stack tb-end" :style="cols.price ? '' : 'visibility:hidden'">
                <strong>€ {{ number_format(((int) $record->base_price_per_night) / 100, 0) }}</strong>
                <small>min. {{ $nights }} night{{ $nights === 1 ? '' : 's' }}</small>
            </span>
            <span :style="cols.host ? '' : 'visibility:hidden'">{{ $record->host?->name }}</span>
            <span :style="cols.status ? '' : 'visibility:hidden'"><x-admin.status-pill :tone="$tone">{{ \App\Support\AdminTableBadgeColors::humanize($record->status) }}</x-admin.status-pill></span>
            <span class="tb-muted" :style="cols.updated ? '' : 'visibility:hidden'">{{ $record->updated_at?->format('j M Y') }}</span>
            @php
                $menu = [];
                if (filled($record->slug)) {
                    $menu[] = ['label' => 'Preview', 'icon' => 'icon-tb-eye.svg', 'href' => $frontend.'/guesthouses/'.$record->slug, 'external' => true];
                }
                $menu[] = ['label' => 'Duplicate', 'icon' => 'icon-tb-copy.svg', 'wire' => "mountTableAction('duplicate', '{$key}')"];
                if ($status === 'pending_review') {
                    $menu[] = ['label' => 'Approve', 'wire' => "mountTableAction('approve', '{$key}')"];
                    $menu[] = ['label' => 'Reject', 'wire' => "mountTableAction('reject', '{$key}')"];
                    $menu[] = ['label' => 'Request changes', 'wire' => "mountTableAction('requestChanges', '{$key}')"];
                }
                $menu[] = ['label' => 'Delete…', 'icon' => 'icon-tb-trash.svg', 'wire' => "mountTableAction('delete', '{$key}')", 'danger' => true];
            @endphp
            <x-admin.row-menu :edit-url="GuestHouseResource::getUrl('edit', ['record' => $record])" :items="$menu" />
        </div>
    @empty
        <p class="tb-empty">No guest houses match these filters.</p>
    @endforelse

    <x-admin.table-footer :records="$records" :page-name="$pageName" />
</div>

</div>
<x-filament-actions::modals />
