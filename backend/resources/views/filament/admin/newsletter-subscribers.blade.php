@php
    use App\Enums\NewsletterCampaignStatus;
    use App\Models\NewsletterCampaign;
    use App\Models\NewsletterSubscriber;
    use App\Support\AdminTableBadgeColors;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $sourceValue = data_get($this->tableFilters, 'source.value');
    $statusValue = data_get($this->tableFilters, 'is_active.value');
    $sources = NewsletterSubscriber::query()->whereNotNull('source')->where('source', '!=', '')->distinct()->orderBy('source')->pluck('source');
    $active = NewsletterSubscriber::query()->where('is_active', true)->count();
    $unsubscribed = NewsletterSubscriber::query()->where('is_active', false)->count();
    $sent = NewsletterCampaign::query()->where('status', NewsletterCampaignStatus::Sent)->count();
@endphp

<div class="tb-list" x-data="{ openFilter: null }">
    <div class="tb-stats">
        <div class="tb-stat"><span>Active subscribers</span><strong>{{ $active }}</strong></div>
        <div class="tb-stat"><span>Unsubscribed</span><strong>{{ $unsubscribed }}</strong></div>
        <div class="tb-stat"><span>Campaigns sent</span><strong>{{ $sent }}</strong></div>
    </div>
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search e-mail…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($sourceValue)]) x-on:click="openFilter = openFilter === 'source' ? null : 'source'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($sourceValue) ? AdminTableBadgeColors::humanize((string) $sourceValue) : 'Source' }}
                </button>
                <select x-cloak x-show="openFilter === 'source'" wire:model.live="tableFilters.source.value">
                    <option value="">Any</option>
                    @foreach ($sources as $source)
                        <option value="{{ $source }}">{{ AdminTableBadgeColors::humanize((string) $source) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($statusValue)]) x-on:click="openFilter = openFilter === 'status' ? null : 'status'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ in_array($statusValue, [true, 1, '1'], true) ? 'Active' : (in_array($statusValue, [false, 0, '0'], true) ? 'Unsubscribed' : 'Status') }}
                </button>
                <select x-cloak x-show="openFilter === 'status'" wire:model.live="tableFilters.is_active.value">
                    <option value="">Any</option>
                    <option value="1">Active</option>
                    <option value="0">Unsubscribed</option>
                </select>
            </div>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:minmax(200px,1.6fr) 130px 130px 160px 130px">
            <span>E-mail</span><span>Status</span><span>Source</span><span>Subscribed</span><span></span>
        </div>
        @forelse ($records as $record)
            @php $key = $getRecordKey($record); @endphp
            <div class="tb-row tb-grid" wire:key="subscriber-{{ $key }}" style="grid-template-columns:minmax(200px,1.6fr) 130px 130px 160px 130px">
                <span>{{ $record->email }}</span>
                <span><x-admin.status-pill :tone="$record->is_active ? 'ok' : 'muted'">{{ $record->is_active ? 'Active' : 'Unsubscribed' }}</x-admin.status-pill></span>
                <span>@if ($record->source)<x-admin.status-pill tone="info">{{ AdminTableBadgeColors::humanize((string) $record->source) }}</x-admin.status-pill>@else — @endif</span>
                <span>{{ $record->subscribed_at?->format('j M Y, H:i') ?: '—' }}</span>
                <span>
                    @if ($record->is_active)
                        <button type="button" class="tb-mini-btn" wire:click="mountTableAction('deactivate', '{{ $key }}')">Unsubscribe</button>
                    @else
                        <button type="button" class="tb-mini-btn" wire:click="mountTableAction('reactivate', '{{ $key }}')">Reactivate</button>
                    @endif
                </span>
            </div>
        @empty
            <p class="tb-empty">No subscribers match this search.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
