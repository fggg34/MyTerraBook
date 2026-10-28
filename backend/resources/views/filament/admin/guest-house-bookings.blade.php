@php
    use App\Enums\GuestHouseBookingStatus;
    use App\Filament\GuestHouse\Resources\GuestHouseBookingResource;
    use App\Models\GuestHouse;
    use App\Support\AdminMoney;
    use App\Support\AdminTableBadgeColors;
    use App\Support\BookingConfirmationUrl;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $houses = GuestHouse::query()->orderBy('name')->pluck('name', 'id');
    $houseValue = data_get($this->tableFilters, 'guest_house_id.value');
    $statusValue = data_get($this->tableFilters, 'status.value');
    $fromValue = data_get($this->tableFilters, 'date_range.from');
@endphp

<div class="tb-list" x-data="{ openFilter: null }">
    <x-admin.status-tabs :tabs="$this->getTabs()" :active="$this->activeTab ?? 'all'" />
    <div class="tb-table-card">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Reference or guest…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($houseValue)]) x-on:click="openFilter = openFilter === 'house' ? null : 'house'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($houseValue) ? ($houses[$houseValue] ?? 'Property') : 'Property' }}
                </button>
                <select x-cloak x-show="openFilter === 'house'" wire:model.live="tableFilters.guest_house_id.value">
                    <option value="">Any</option>
                    @foreach ($houses as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($fromValue)]) x-on:click="openFilter = openFilter === 'in' ? null : 'in'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    Check-in date
                </button>
                <input x-cloak x-show="openFilter === 'in'" type="date" wire:model.live="tableFilters.date_range.from" aria-label="Check-in from">
                <input x-cloak x-show="openFilter === 'in'" type="date" wire:model.live="tableFilters.date_range.to" aria-label="Check-out until">
            </div>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($statusValue)]) x-on:click="openFilter = openFilter === 'status' ? null : 'status'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($statusValue) ? AdminTableBadgeColors::humanize($statusValue) : 'Status' }}
                </button>
                <select x-cloak x-show="openFilter === 'status'" wire:model.live="tableFilters.status.value">
                    <option value="">Any</option>
                    @foreach (GuestHouseBookingStatus::cases() as $case)
                        <option value="{{ $case->value }}">{{ AdminTableBadgeColors::humanize($case) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:minmax(140px,.9fr) minmax(120px,.8fr) minmax(160px,1.2fr) minmax(140px,1fr) 90px 110px 150px">
            <span>Reference</span><span>Guest</span><span>Property</span><span>Stay</span><span>Total</span><span>Status</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $record->getKey();
                $pending = $record->status === GuestHouseBookingStatus::Pending;
                $canCancel = in_array($record->status, [GuestHouseBookingStatus::Pending, GuestHouseBookingStatus::Confirmed], true);
                $tone = match ($record->status) {
                    GuestHouseBookingStatus::Confirmed, GuestHouseBookingStatus::Completed => 'ok',
                    GuestHouseBookingStatus::Cancelled => 'bad',
                    default => 'warn',
                };
                $confirmUrl = $record->confirmation_url ?: ($record->confirmation_token ? BookingConfirmationUrl::forToken($record->confirmation_token) : null);
                $menu = [];
                if (filled($confirmUrl)) {
                    $menu[] = ['label' => 'Confirmation page', 'href' => $confirmUrl, 'external' => true];
                }
                if (in_array($record->status, [GuestHouseBookingStatus::Confirmed, GuestHouseBookingStatus::Completed], true)) {
                    $menu[] = ['label' => 'PDF', 'href' => route('admin.guest-house-bookings.contract-pdf', ['booking' => $record->id]), 'external' => true];
                }
                if ($canCancel && ! $pending) {
                    $menu[] = ['label' => 'Cancel', 'wire' => "mountTableAction('cancel', '{$key}')", 'danger' => true];
                }
            @endphp
            <div class="tb-row tb-grid" wire:key="stay-{{ $key }}" style="grid-template-columns:minmax(140px,.9fr) minmax(120px,.8fr) minmax(160px,1.2fr) minmax(140px,1fr) 90px 110px 150px">
                <span>{{ $record->booking_reference }}</span>
                <span>{{ $record->guest_name ?: '—' }}</span>
                <span class="tb-stack">
                    @if ($record->guestHouse)
                        <strong>{{ $record->guestHouse->name }}</strong>
                        <small>{{ $record->guestHouse->city }}</small>
                    @else
                        <span class="tb-warn-text">No property linked</span>
                    @endif
                </span>
                <span class="tb-stack">
                    <strong>{{ $record->check_in?->format('j M') }} → {{ $record->check_out?->format('j M Y') }}</strong>
                    <small>{{ (int) $record->nights }} {{ (int) $record->nights === 1 ? 'night' : 'nights' }}</small>
                </span>
                <span>{{ $record->total_amount ? '€ '.AdminMoney::group(intdiv((int) $record->total_amount, 100)) : '—' }}</span>
                <span><x-admin.status-pill :tone="$tone">{{ AdminTableBadgeColors::humanize($record->status ?? 'pending') }}</x-admin.status-pill></span>
                <span class="tb-actions tb-keep">
                    @if ($pending)
                        <button type="button" class="tb-btn tb-btn-sm tb-btn-primary" wire:click="mountTableAction('confirm', '{{ $key }}')">Confirm</button>
                        <button type="button" class="tb-btn tb-btn-sm" wire:click="mountTableAction('cancel', '{{ $key }}')">Cancel</button>
                    @else
                        <x-admin.row-menu :edit-url="GuestHouseBookingResource::getUrl('view', ['record' => $record])" :items="$menu" />
                    @endif
                </span>
            </div>
        @empty
            <p class="tb-empty">No guest house bookings match these filters.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
