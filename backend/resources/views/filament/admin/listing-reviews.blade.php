@php
    use App\Filament\Resources\ListingReviews\ListingReviewResource;
    use App\Models\Car;
    use App\Models\GuestHouse;
    use App\Models\ListingReview;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $ratingValue = data_get($this->tableFilters, 'rating.value');
    $typeValue = data_get($this->tableFilters, 'reviewable_type.value');
    $visibleValue = data_get($this->tableFilters, 'is_approved.value');
    $visibleTab = in_array($visibleValue, [true, 1, '1'], true) ? 'visible' : (in_array($visibleValue, [false, 0, '0'], true) ? 'hidden' : 'all');
    $types = [Car::class => 'Vehicle', GuestHouse::class => 'Guest house'];
    $base = ListingReview::query();
    $counts = [
        'all' => (clone $base)->count(),
        'visible' => (clone $base)->where('is_approved', true)->count(),
        'hidden' => (clone $base)->where('is_approved', false)->count(),
    ];
@endphp

<div class="tb-list" x-data="{ openFilter: null }">
    <div class="tb-seg tb-api-tabs" role="tablist" aria-label="Visibility">
        <button type="button" @class(['is-active' => $visibleTab === 'all']) wire:click="$set('tableFilters.is_approved.value', null)">All <span>{{ $counts['all'] }}</span></button>
        <button type="button" @class(['is-active' => $visibleTab === 'visible']) wire:click="$set('tableFilters.is_approved.value', true)">Visible <span>{{ $counts['visible'] }}</span></button>
        <button type="button" @class(['is-active' => $visibleTab === 'hidden']) wire:click="$set('tableFilters.is_approved.value', false)">Hidden <span>{{ $counts['hidden'] }}</span></button>
    </div>
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search guest or listing…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($ratingValue)]) x-on:click="openFilter = openFilter === 'rating' ? null : 'rating'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($ratingValue) ? $ratingValue.' star'.((int) $ratingValue === 1 ? '' : 's') : 'Rating' }}
                </button>
                <select x-cloak x-show="openFilter === 'rating'" wire:model.live="tableFilters.rating.value">
                    <option value="">Any</option>
                    @foreach ([1, 2, 3, 4, 5] as $star)
                        <option value="{{ $star }}">{{ $star }} {{ $star === 1 ? 'star' : 'stars' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($typeValue)]) x-on:click="openFilter = openFilter === 'type' ? null : 'type'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ $types[$typeValue] ?? 'Type' }}
                </button>
                <select x-cloak x-show="openFilter === 'type'" wire:model.live="tableFilters.reviewable_type.value">
                    <option value="">Any</option>
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-mini-btn" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('approve')">Approve</button>
            <button type="button" class="tb-mini-btn" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('hide')">Hide</button>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        @if ($counts['all'] === 0)
            <div class="tb-empty-card">
                <strong>No listing reviews yet</strong>
                @if (ListingReviewResource::canCreate())
                    <a class="tb-btn tb-btn-md tb-btn-primary" href="{{ ListingReviewResource::getUrl('create') }}">New listing review</a>
                @endif
            </div>
        @else
            <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(140px,1.2fr) 90px 120px minmax(160px,1.4fr) 80px 140px 56px">
                <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
                <span>Guest</span><span>Rating</span><span>Type</span><span>Listing</span><span>Visible</span><span>Submitted</span><span></span>
            </div>
            @forelse ($records as $record)
                @php
                    $key = $getRecordKey($record);
                    $menu = [
                        ['label' => 'Edit', 'href' => ListingReviewResource::getUrl('edit', ['record' => $record])],
                    ];
                @endphp
                <div class="tb-row tb-grid" wire:key="review-{{ $key }}" style="grid-template-columns:20px minmax(140px,1.2fr) 90px 120px minmax(160px,1.4fr) 80px 140px 56px">
                    <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                    <span>{{ $record->guest_name ?: '—' }}</span>
                    <span class="tb-stars">{{ str_repeat('★', (int) $record->rating) }}{{ str_repeat('☆', max(0, 5 - (int) $record->rating)) }}</span>
                    <span>{{ $types[$record->reviewable_type] ?? class_basename((string) $record->reviewable_type) }}</span>
                    <span>{{ $record->listingName() }}</span>
                    <span><x-admin.switch :on="(bool) $record->is_approved" wire="toggleVisible({{ $key }})" label="Visible" /></span>
                    <span>{{ $record->created_at?->format('j M Y, H:i') ?: '—' }}</span>
                    <x-admin.row-menu :items="$menu" />
                </div>
            @empty
                <p class="tb-empty">No reviews match this search.</p>
            @endforelse
            <x-admin.table-footer :records="$records" :page-name="$pageName" />
        @endif
    </div>
</div>
<x-filament-actions::modals />
