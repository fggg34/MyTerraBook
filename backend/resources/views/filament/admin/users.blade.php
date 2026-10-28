@php
    use App\Enums\UserRole;
    use App\Support\AdminTableBadgeColors;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $role = $this->tableFilters['role']['value'] ?? null;
@endphp

<div class="tb-list">
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search by name or email…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <label class="tb-chip">Role
                <select wire:model.live="tableFilters.role.value">
                    <option value="">All</option>
                    @foreach (UserRole::cases() as $case)
                        <option value="{{ $case->value }}" @selected($role === $case->value)>{{ AdminTableBadgeColors::humanize($case->value) }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:minmax(120px,1fr) minmax(160px,1.3fr) 90px 120px 110px 150px">
            <span>Name</span><span>E-mail</span><span>Role</span><span>Phone</span><span>Registered</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $roleValue = $record->role instanceof UserRole ? $record->role->value : (string) $record->role;
            @endphp
            <div class="tb-row tb-grid" wire:key="user-{{ $key }}" style="grid-template-columns:minmax(120px,1fr) minmax(160px,1.3fr) 90px 120px 110px 150px">
                <span>{{ $record->name }}</span>
                <span>{{ $record->email }}</span>
                <span>{{ AdminTableBadgeColors::humanize($roleValue) }}</span>
                <span>{{ $record->phone ?: '—' }}</span>
                <span>{{ optional($record->created_at)->format('j M Y') }}</span>
                <button type="button" class="tb-text-btn" wire:click="mountTableAction('changePassword', @js($key))">Change password</button>
            </div>
        @empty
            <p class="tb-empty">No users found.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
