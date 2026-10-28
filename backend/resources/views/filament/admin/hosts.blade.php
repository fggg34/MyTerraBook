@php
    use App\Enums\UserRole;
    use App\Filament\Resources\RegisteredHosts\RegisteredHostResource;
    use App\Models\User;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $countries = config('destinations', []);
    $countryValue = data_get($this->tableFilters, 'country_code.value');
    $providerValue = data_get($this->tableFilters, 'partner_provider.value');
    $hasApi = \Illuminate\Support\Facades\Schema::hasColumn('users', 'partner_api_enabled');
    $hasRate = \Illuminate\Support\Facades\Schema::hasColumn('users', 'partner_commission_rate');
    $hasProvider = \Illuminate\Support\Facades\Schema::hasColumn('users', 'partner_provider');
    $apiValue = data_get($this->tableFilters, 'partner_api_enabled.value');
    $apiTab = in_array($apiValue, [true, 1, '1'], true) ? 'enabled' : (in_array($apiValue, [false, 0, '0'], true) ? 'disabled' : 'all');
    $providers = ['greenlight' => 'Greenlight', 'caren' => 'Caren', 'foxie' => 'Foxie Camper', 'custom' => 'Custom API'];
    $hostQuery = User::query()->where('role', UserRole::Host);
    $counts = [
        'all' => (clone $hostQuery)->count(),
        'enabled' => $hasApi ? (clone $hostQuery)->where('partner_api_enabled', true)->count() : null,
        'disabled' => $hasApi ? (clone $hostQuery)->where('partner_api_enabled', false)->count() : null,
    ];
@endphp

<div class="tb-list" x-data="{ openFilter: null }">
    @if ($hasApi)
    <div class="tb-seg tb-api-tabs" role="tablist" aria-label="Partner API">
        <button type="button" @class(['is-active' => $apiTab === 'all']) wire:click="$set('tableFilters.partner_api_enabled.value', null)">All <span>{{ $counts['all'] }}</span></button>
        <button type="button" @class(['is-active' => $apiTab === 'enabled']) wire:click="$set('tableFilters.partner_api_enabled.value', true)">API enabled <span>{{ $counts['enabled'] }}</span></button>
        <button type="button" @class(['is-active' => $apiTab === 'disabled']) wire:click="$set('tableFilters.partner_api_enabled.value', false)">API disabled <span>{{ $counts['disabled'] }}</span></button>
    </div>
    @endif
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search hosts by name…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($countryValue)]) x-on:click="openFilter = openFilter === 'country' ? null : 'country'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($countryValue) ? ($countries[$countryValue] ?? $countryValue) : 'Country' }}
                </button>
                <select x-cloak x-show="openFilter === 'country'" wire:model.live="tableFilters.country_code.value">
                    <option value="">Any</option>
                    @foreach ($countries as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($hasProvider)
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($providerValue)]) x-on:click="openFilter = openFilter === 'provider' ? null : 'provider'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($providerValue) ? ($providers[$providerValue] ?? $providerValue) : 'Connection' }}
                </button>
                <select x-cloak x-show="openFilter === 'provider'" wire:model.live="tableFilters.partner_provider.value">
                    <option value="">Any</option>
                    @foreach ($providers as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(180px,1.6fr) 110px 80px 70px 120px minmax(120px,1fr) 150px">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Host / partner</span><span>Country</span><span>Vehicles</span><span>Stays</span><span>Partner API</span><span>Commission</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $words = preg_split('/\s+/', trim((string) $record->name)) ?: [];
                $initials = strtoupper(mb_substr((string) ($words[0] ?? ''), 0, 1).mb_substr((string) ($words[1] ?? ''), 0, 1));
                $country = $record->country_code ? (string) ($countries[$record->country_code] ?? $record->country_code) : '—';
                $rate = $hasRate ? $record->partner_commission_rate : null;
                $commission = ! $hasRate
                    ? '—'
                    : ($rate !== null
                        ? rtrim(rtrim((string) $rate, '0'), '.').'%'
                        : rtrim(rtrim(number_format(((float) config('rapyd.commission_rate', 0.15)) * 100, 2, '.', ''), '0'), '.').'% · Default');
                $menu = [
                    ['label' => 'Manage host', 'href' => RegisteredHostResource::getUrl('edit', ['record' => $record])],
                    ['label' => 'View', 'href' => RegisteredHostResource::getUrl('view', ['record' => $record])],
                    ['label' => 'Delete', 'wire' => "mountTableAction('delete', '{$key}')", 'danger' => true],
                ];
            @endphp
            <div class="tb-row tb-grid" wire:key="host-{{ $key }}" style="grid-template-columns:20px minmax(180px,1.6fr) 110px 80px 70px 120px minmax(120px,1fr) 150px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <x-admin.entity-cell :title="$record->name" :subtitle="$record->email" :initials="$initials ?: '—'" round />
                <span>{{ $country }}</span>
                <span>{{ (int) $record->cars_count }}</span>
                <span>{{ (int) $record->guest_houses_count }}</span>
                <span>
                    @if ($hasApi)
                        <x-admin.status-pill :tone="$record->partner_api_enabled ? 'ok' : 'muted'">{{ $record->partner_api_enabled ? 'Enabled' : 'Disabled' }}</x-admin.status-pill>
                    @else
                        —
                    @endif
                </span>
                <span>{{ $commission }}</span>
                <span class="tb-actions tb-keep">
                    <button type="button" class="tb-mini-btn" wire:click="mountTableAction('sendHostLogin', '{{ $key }}')">Send login</button>
                    <x-admin.row-menu :items="$menu" />
                </span>
            </div>
        @empty
            <p class="tb-empty">No hosts match this search.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
