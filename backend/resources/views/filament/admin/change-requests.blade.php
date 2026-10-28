@php
    use App\Enums\BookingChangeRequestType;
    use App\Filament\GuestHouse\Resources\GuestHouseBookingResource;
    use App\Filament\Resources\BookingChangeRequests\BookingChangeRequestResource;
    use App\Filament\Resources\Orders\OrderResource;
    use App\Models\GuestHouseBooking;
    use App\Models\Order;
    use App\Support\AdminMoney;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $typeValue = data_get($this->tableFilters, 'type.value');
@endphp

<div class="tb-list" x-data="{ openFilter: null }">
    <x-admin.status-tabs :tabs="$this->getTabs()" :active="$this->activeTab ?? 'all'" />
    <div class="tb-table-card">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Booking or customer…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($typeValue)]) x-on:click="openFilter = openFilter === 'type' ? null : 'type'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($typeValue) ? ucfirst($typeValue) : 'Type' }}
                </button>
                <select x-cloak x-show="openFilter === 'type'" wire:model.live="tableFilters.type.value">
                    <option value="">Any</option>
                    @foreach (BookingChangeRequestType::cases() as $case)
                        <option value="{{ $case->value }}">{{ ucfirst($case->value) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:48px minmax(160px,1fr) 130px minmax(160px,1.2fr) 120px 110px 110px 40px">
            <span>#</span><span>Booking</span><span>Request</span><span>Customer message</span><span>Price change</span><span>Status</span><span>Created</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $record->getKey();
                $booking = $record->bookable;
                $bookingUrl = null;
                $bookingLabel = 'Booking #'.$record->bookable_id;
                $bookingSub = null;
                $isOrder = $booking instanceof Order || $record->bookable_type === Order::class;
                $isStay = $booking instanceof GuestHouseBooking || $record->bookable_type === GuestHouseBooking::class;
                if ($isOrder) {
                    $bookingLabel = 'Car reservation';
                    $bookingSub = $booking ? ($booking->reference ?: '#'.$booking->id) : '#'.$record->bookable_id;
                    $bookingUrl = $booking ? OrderResource::getUrl('edit', ['record' => $booking]) : null;
                } elseif ($isStay) {
                    $bookingLabel = 'Guest house booking';
                    $bookingSub = $booking?->booking_reference ?: '#'.$record->bookable_id;
                    $bookingUrl = $booking ? GuestHouseBookingResource::getUrl('view', ['record' => $booking]) : null;
                }
                $delta = $record->price_delta_cents;
                $tone = match ($record->status?->value) {
                    'applied', 'approved' => 'ok',
                    'rejected' => 'bad',
                    default => 'warn',
                };
                $menu = [];
                if ($record->status?->value === 'pending') {
                    $menu[] = ['label' => 'Apply & update booking', 'wire' => "mountTableAction('apply', '{$key}')"];
                    $menu[] = ['label' => 'Reject', 'wire' => "mountTableAction('reject', '{$key}')", 'danger' => true];
                }
            @endphp
            <div class="tb-row tb-grid" wire:key="change-{{ $key }}" style="grid-template-columns:48px minmax(160px,1fr) 130px minmax(160px,1.2fr) 120px 110px 110px 40px">
                <span>{{ $record->id }}</span>
                <span class="tb-stack">
                    <strong>{{ $bookingLabel }}@if ($bookingSub) {{ $bookingSub }}@endif</strong>
                    @if ($bookingUrl)
                        <small><a href="{{ $bookingUrl }}">Open booking</a></small>
                    @endif
                </span>
                <span><x-admin.status-pill tone="info">{{ ucfirst($record->type?->value ?? 'modification') }}</x-admin.status-pill></span>
                <span>{{ filled($record->customer_message) ? '“'.$record->customer_message.'”' : '—' }}</span>
                <span @class(['tb-warn-text' => is_int($delta) && $delta > 0])>
                    @if ($delta === null)
                        —
                    @else
                        {{ $delta > 0 ? '+' : '' }}{{ AdminMoney::fromCents((int) $delta) }}
                    @endif
                </span>
                <span><x-admin.status-pill :tone="$tone">{{ ucfirst($record->status?->value ?? 'pending') }}</x-admin.status-pill></span>
                <span>{{ $record->created_at?->format('j M Y') }}</span>
                <x-admin.row-menu :edit-url="BookingChangeRequestResource::getUrl('view', ['record' => $record])" icon="icon-tb-eye.svg" :items="$menu" />
            </div>
        @empty
            <p class="tb-empty">No change requests match these filters.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
