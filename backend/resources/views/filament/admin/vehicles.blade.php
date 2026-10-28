@php
    use App\Enums\DriveType;
    use App\Enums\ListingApprovalStatus;
    use App\Filament\Resources\Cars\CarResource;
    use App\Models\Car;
    use App\Models\Location;
    use App\Models\SubCategory;
    use App\Models\User;
    use App\Support\AdminTableBadgeColors;
    use Illuminate\Support\Facades\Storage;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $grid = $this->viewMode !== 'table';
    $categories = SubCategory::query()->orderBy('name')->pluck('name', 'id');
    $hosts = User::query()->whereIn('id', Car::query()->whereNotNull('user_id')->distinct()->pluck('user_id'))->orderBy('name')->pluck('name', 'id');
    $drives = collect(DriveType::cases())->mapWithKeys(fn (DriveType $type): array => [$type->value => $type->label()]);
    $statuses = collect(ListingApprovalStatus::cases())->mapWithKeys(fn (ListingApprovalStatus $status): array => [$status->value => AdminTableBadgeColors::humanize($status->value)]);
    $locations = Location::query()->orderBy('name')->pluck('name', 'id');
    $categoryValue = data_get($this->tableFilters, 'sub_category_id.value');
    $hostValue = data_get($this->tableFilters, 'user_id.value');
    $driveValue = data_get($this->tableFilters, 'drive_type.value');
    $reviewValue = data_get($this->tableFilters, 'listing_status.value');
    $locationValue = data_get($this->tableFilters, 'locations.value');
    $activeValue = data_get($this->tableFilters, 'is_active.value');
    $moreCount = collect([$reviewValue, $locationValue, $activeValue])->filter(fn ($value) => filled($value))->count();
@endphp

<div class="tb-list" x-data="{ colsOpen: false, openFilter: null, moreOpen: false, cols: { category: true, drive: true, status: true, review: true, host: true, transmission: false, fuel: false, slug: false, fleet: false, ical: false, submitted: false, created: false, updated: false } }">
    <x-admin.status-tabs :tabs="$this->getTabs()" :active="$this->activeTab ?? 'all'" />

    <div
        @class(['tb-table-card' => ! $grid])
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

        <div @class(['tb-toolbar' => ! $grid, 'tb-toolbar tb-toolbar-plain' => $grid])>
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search vehicle or host…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            @foreach ([
                'sub_category_id' => ['Category', $categories, $categoryValue],
                'user_id' => ['Host', $hosts, $hostValue],
                'drive_type' => ['Drivetrain', $drives, $driveValue],
            ] as $filter => [$label, $options, $current])
                <div class="tb-chip-menu">
                    <button type="button" @class(['tb-chip', 'is-set' => filled($current)]) x-on:click="openFilter = openFilter === '{{ $filter }}' ? null : '{{ $filter }}'">
                        <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                        {{ filled($current) ? ($options[$current] ?? $label) : $label }}
                    </button>
                    <select x-cloak x-show="openFilter === '{{ $filter }}'" wire:model.live="tableFilters.{{ $filter }}.value">
                        <option value="">Any</option>
                        @foreach ($options as $value => $optionLabel)
                            <option value="{{ $value }}">{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="tb-more-filters">
                <button type="button" @class(['tb-chip', 'is-set' => $moreCount > 0]) x-on:click="moreOpen = !moreOpen">
                    More filters
                    @if ($moreCount > 0)
                        <span class="tb-count">{{ $moreCount }}</span>
                    @endif
                </button>
                <menu x-cloak x-show="moreOpen">
                    <label>Review status
                        <select wire:model.live="tableFilters.listing_status.value">
                            <option value="">Any</option>
                            @foreach ($statuses as $value => $optionLabel)
                                <option value="{{ $value }}">{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Location
                        <select wire:model.live="tableFilters.locations.value">
                            <option value="">Any</option>
                            @foreach ($locations as $value => $optionLabel)
                                <option value="{{ $value }}">{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>Active
                        <select wire:model.live="tableFilters.is_active.value">
                            <option value="">Any</option>
                            <option value="1">Active vehicles</option>
                            <option value="0">Inactive vehicles</option>
                        </select>
                    </label>
                </menu>
            </div>
            <div class="tb-toolbar-spacer"></div>
            @unless ($grid)
                <button type="button" class="tb-btn tb-btn-sm" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                    <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
                    Bulk actions
                </button>
                <div class="tb-columns">
                    <button type="button" class="tb-icon-btn" aria-label="Columns" x-on:click="colsOpen = !colsOpen">
                        <img src="{{ asset('admin-redesign/icon-tb-columns.svg') }}" alt="" width="16" height="16">
                    </button>
                    <menu x-cloak x-show="colsOpen">
                        @foreach (['category' => 'Category', 'drive' => 'Drivetrain', 'status' => 'Status', 'review' => 'Review', 'host' => 'Host / partner', 'transmission' => 'Transmission', 'fuel' => 'Fuel', 'slug' => 'Slug', 'fleet' => 'Fleet size', 'ical' => 'iCal import URL', 'submitted' => 'Submitted', 'created' => 'Created', 'updated' => 'Updated'] as $key => $label)
                            <li><label><input type="checkbox" x-model="cols.{{ $key }}"> {{ $label }}</label></li>
                        @endforeach
                    </menu>
                </div>
            @endunless
            <div class="tb-view-toggle" role="group" aria-label="Layout">
                <button type="button" @class(['is-active' => $grid]) wire:click="$set('viewMode', 'grid')" aria-label="Card view">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="1.5" y="1.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="9.5" y="1.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="1.5" y="9.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/><rect x="9.5" y="9.5" width="5" height="5" rx="1" stroke="currentColor" stroke-width="1.5"/></svg>
                </button>
                <button type="button" @class(['is-active' => ! $grid]) wire:click="$set('viewMode', 'table')" aria-label="Table view">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><rect x="1.5" y="2.5" width="13" height="11" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M1.5 6.5h13M1.5 10h13M6 2.5v11" stroke="currentColor" stroke-width="1.5"/></svg>
                </button>
            </div>
        </div>

        @if ($grid)
            <div class="tb-cards">
                @forelse ($records as $record)
                    @include('filament.admin.partials.vehicle-card', ['car' => $record])
                @empty
                    <p class="tb-empty">No vehicles match these filters.</p>
                @endforelse
            </div>
        @else
            <div class="tb-head tb-grid" :style="(() => { const t = ['20px','minmax(180px,1.4fr)']; const m = {category:'minmax(110px,1fr)',drive:'84px',status:'84px',review:'96px',host:'minmax(110px,1fr)',transmission:'90px',fuel:'80px',slug:'120px',fleet:'72px',ical:'140px',submitted:'110px',created:'110px',updated:'110px'}; for (const k in m) if (cols[k]) t.push(m[k]); t.push('56px'); return 'grid-template-columns:'+t.join(' '); })()">
                <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
                <span>Vehicle</span>
                <template x-if="cols.category"><span>Category</span></template>
                <template x-if="cols.drive"><span>Drivetrain</span></template>
                <template x-if="cols.status"><span>Status</span></template>
                <template x-if="cols.review"><span>Review</span></template>
                <template x-if="cols.host"><span>Host / partner</span></template>
                <template x-if="cols.transmission"><span>Transmission</span></template>
                <template x-if="cols.fuel"><span>Fuel</span></template>
                <template x-if="cols.slug"><span>Slug</span></template>
                <template x-if="cols.fleet"><span>Fleet size</span></template>
                <template x-if="cols.ical"><span>iCal import URL</span></template>
                <template x-if="cols.submitted"><span>Submitted</span></template>
                <template x-if="cols.created"><span>Created</span></template>
                <template x-if="cols.updated"><span>Updated</span></template>
                <span></span>
            </div>
            @forelse ($records as $record)
                @php
                    $key = $getRecordKey($record);
                    $path = (string) ($record->main_image_path ?? '');
                    $photo = null;
                    if ($path !== '') {
                        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                            $photo = $path;
                        } elseif (Storage::disk('public')->exists($path)) {
                            $photo = Storage::disk('public')->url($path);
                        }
                    }
                    $units = max(1, (int) ($record->units_available ?? 1));
                    $subline = collect([$units === 1 ? '1 unit' : $units.' units', $record->year])->filter(fn ($part) => filled($part))->implode(' · ');
                    $driveLabel = $record->drive_type instanceof DriveType ? $record->drive_type->label() : (string) ($record->drive_type ?? '–');
                    $review = $record->listing_status instanceof ListingApprovalStatus ? $record->listing_status->value : (string) $record->listing_status;
                    $reviewTone = match ($review) {
                        'approved' => 'ok',
                        'pending_review' => 'warn',
                        default => 'bad',
                    };
                    $menu = [
                        ['label' => 'Duplicate', 'icon' => 'icon-tb-copy.svg', 'wire' => "mountTableAction('duplicate', '{$key}')"],
                    ];
                    if ($review === 'pending_review') {
                        $menu[] = ['label' => 'Approve', 'wire' => "mountTableAction('approve', '{$key}')"];
                        $menu[] = ['label' => 'Reject', 'wire' => "mountTableAction('reject', '{$key}')"];
                        $menu[] = ['label' => 'Request changes', 'wire' => "mountTableAction('requestChanges', '{$key}')"];
                    }
                    $menu[] = ['label' => 'Delete…', 'icon' => 'icon-tb-trash.svg', 'wire' => "mountTableAction('delete', '{$key}')", 'danger' => true];
                @endphp
                <div class="tb-row tb-grid" wire:key="car-{{ $key }}" :style="(() => { const t = ['20px','minmax(180px,1.4fr)']; const m = {category:'minmax(110px,1fr)',drive:'84px',status:'84px',review:'96px',host:'minmax(110px,1fr)',transmission:'90px',fuel:'80px',slug:'120px',fleet:'72px',ical:'140px',submitted:'110px',created:'110px',updated:'110px'}; for (const k in m) if (cols[k]) t.push(m[k]); t.push('56px'); return 'grid-template-columns:'+t.join(' '); })()">
                    <span>
                        <input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null">
                    </span>
                    <x-admin.entity-cell :title="$record->name" :subtitle="$subline" :image="$photo" :round="true" :missing="blank($photo)" />
                    <template x-if="cols.category"><span class="tb-stack"><strong>{{ $record->subCategory?->mainCategory?->name ?: '–' }}</strong><small>{{ $record->subCategory?->name }}</small></span></template>
                    <template x-if="cols.drive"><span>{{ $driveLabel ?: '–' }}</span></template>
                    <template x-if="cols.status"><span><x-admin.status-pill :tone="$record->is_active ? 'ok' : 'bad'">{{ $record->is_active ? 'Active' : 'Inactive' }}</x-admin.status-pill></span></template>
                    <template x-if="cols.review"><span><x-admin.status-pill :tone="$reviewTone">{{ AdminTableBadgeColors::humanize($review) }}</x-admin.status-pill></span></template>
                    <template x-if="cols.host"><span>{{ $record->host?->name ?: (filled($record->external_provider) ? ucfirst((string) $record->external_provider) : 'Platform fleet') }}</span></template>
                    <template x-if="cols.transmission"><span>{{ filled($record->transmission) ? ucfirst((string) $record->transmission) : '–' }}</span></template>
                    <template x-if="cols.fuel"><span>{{ filled($record->fuel_type) ? ucfirst((string) $record->fuel_type) : '–' }}</span></template>
                    <template x-if="cols.slug"><span>{{ $record->slug }}</span></template>
                    <template x-if="cols.fleet"><span>{{ $units }}</span></template>
                    <template x-if="cols.ical"><span>{{ \Illuminate\Support\Str::limit((string) $record->ical_import_url, 35) }}</span></template>
                    <template x-if="cols.submitted"><span>{{ $record->submitted_at?->format('j M Y') }}</span></template>
                    <template x-if="cols.created"><span>{{ $record->created_at?->format('j M Y') }}</span></template>
                    <template x-if="cols.updated"><span>{{ $record->updated_at?->format('j M Y') }}</span></template>
                    <x-admin.row-menu :edit-url="CarResource::getUrl('edit', ['record' => $record])" :items="$menu" />
                </div>
            @empty
                <p class="tb-empty">No vehicles match these filters.</p>
            @endforelse
        @endif

        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
