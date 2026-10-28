@php
    use App\Filament\Resources\BlogPosts\BlogPostResource;
    use App\Models\BlogPost;
    use App\Support\AdminTableBadgeColors;
    use Illuminate\Support\Facades\Storage;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $statusValue = data_get($this->tableFilters, 'status.value');
    $featuredValue = data_get($this->tableFilters, 'is_featured.value');
    $kickerValue = data_get($this->tableFilters, 'kicker.value');
    $tab = in_array($featuredValue, [true, 1, '1'], true) ? 'featured' : (in_array($statusValue, ['published', 'draft'], true) ? $statusValue : 'all');
    $kickers = BlogPost::query()->whereNotNull('kicker')->where('kicker', '!=', '')->distinct()->orderBy('kicker')->pluck('kicker');
    $counts = [
        'all' => BlogPost::query()->count(),
        'published' => BlogPost::query()->where('status', 'published')->count(),
        'featured' => BlogPost::query()->where('is_featured', true)->count(),
        'draft' => BlogPost::query()->where('status', 'draft')->count(),
    ];
@endphp

<div class="tb-list" x-data="{ openFilter: null }">
    <div class="tb-seg tb-api-tabs" role="tablist" aria-label="Post status">
        <button type="button" @class(['is-active' => $tab === 'all']) wire:click="setPostTab('all')">All <span>{{ $counts['all'] }}</span></button>
        <button type="button" @class(['is-active' => $tab === 'published']) wire:click="setPostTab('published')">Published <span>{{ $counts['published'] }}</span></button>
        <button type="button" @class(['is-active' => $tab === 'featured']) wire:click="setPostTab('featured')">Featured <span>{{ $counts['featured'] }}</span></button>
        <button type="button" @class(['is-active' => $tab === 'draft']) wire:click="setPostTab('draft')">Draft <span>{{ $counts['draft'] }}</span></button>
    </div>
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search title…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($kickerValue)]) x-on:click="openFilter = openFilter === 'kicker' ? null : 'kicker'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($kickerValue) ? AdminTableBadgeColors::humanize((string) $kickerValue) : 'Kicker' }}
                </button>
                <select x-cloak x-show="openFilter === 'kicker'" wire:model.live="tableFilters.kicker.value">
                    <option value="">Any</option>
                    @foreach ($kickers as $kicker)
                        <option value="{{ $kicker }}">{{ AdminTableBadgeColors::humanize((string) $kicker) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tb-toolbar-spacer"></div>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:minmax(220px,1.8fr) 120px 80px 110px 120px 56px">
            <span>Post</span><span>Kicker</span><span>Featured</span><span>Status</span><span>Published</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $image = filled($record->featured_image) ? Storage::disk('public')->url($record->featured_image) : null;
                $status = $record->status?->value ?? (string) $record->status;
            @endphp
            <div class="tb-row tb-grid" wire:key="post-{{ $key }}" style="grid-template-columns:minmax(220px,1.8fr) 120px 80px 110px 120px 56px">
                <x-admin.entity-cell :title="$record->title" :subtitle="$record->slug ? '/'.$record->slug : null" :image="$image" :missing="! $image" />
                <span>@if ($record->kicker)<x-admin.status-pill tone="info">{{ AdminTableBadgeColors::humanize((string) $record->kicker) }}</x-admin.status-pill>@else — @endif</span>
                <span class="tb-stars">{{ $record->is_featured ? '★' : '—' }}</span>
                <span><x-admin.status-pill :tone="$status === 'published' ? 'ok' : 'muted'">{{ AdminTableBadgeColors::humanize($status) }}</x-admin.status-pill></span>
                <span>{{ $record->published_at?->format('j M Y') ?: '—' }}</span>
                <x-admin.row-menu :edit-url="BlogPostResource::getUrl('edit', ['record' => $record])" :items="[]" />
            </div>
        @empty
            <p class="tb-empty">No posts match this search.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
