@php
    use App\Enums\OrderStatus;
    use App\Enums\RentalStatus;
    use App\Filament\Resources\Orders\OrderResource;
    use App\Models\Car;
    use App\Models\Location;
    use App\Support\AdminMoney;
    use App\Support\AdminTableBadgeColors;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $cars = Car::query()->orderBy('name')->pluck('name', 'id');
    $locations = Location::query()->orderBy('name')->pluck('name', 'id');
    $carValue = data_get($this->tableFilters, 'car.value');
    $pickupValue = data_get($this->tableFilters, 'pickup_location.value');
    $paymentValue = data_get($this->tableFilters, 'payment.value');
    $pickupFrom = data_get($this->tableFilters, 'pickup_from.date');
    $statusLabel = function ($record): string {
        return match ($record->rental_status) {
            RentalStatus::Started => 'On the road',
            RentalStatus::Terminated => 'Returned',
            RentalStatus::NoShow => 'No show',
            default => match ($record->order_status) {
                OrderStatus::StandBy => 'Stand by',
                OrderStatus::Confirmed => 'Confirmed',
                OrderStatus::Cancelled => 'Cancelled',
                default => 'Pending',
            },
        };
    };
    $statusTone = function ($record): string {
        return match ($record->rental_status) {
            RentalStatus::Started => 'info',
            RentalStatus::Terminated => 'ok',
            RentalStatus::NoShow => 'bad',
            default => match ($record->order_status) {
                OrderStatus::Confirmed => 'ok',
                OrderStatus::Cancelled => 'neutral',
                default => 'warn',
            },
        };
    };
@endphp

<div class="tb-list" x-data="{ openFilter: null, more: false }">
    <x-admin.status-tabs :tabs="$this->getTabs()" :active="$this->activeTab ?? 'all'" />
    <div class="tb-table-card" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search bookings…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($pickupFrom)]) x-on:click="openFilter = openFilter === 'pickup' ? null : 'pickup'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    Pickup
                </button>
                <input x-cloak x-show="openFilter === 'pickup'" type="date" wire:model.live="tableFilters.pickup_from.date" aria-label="Pickup from">
            </div>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($carValue)]) x-on:click="openFilter = openFilter === 'car' ? null : 'car'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($carValue) ? ($cars[$carValue] ?? 'Vehicle') : 'Vehicle' }}
                </button>
                <select x-cloak x-show="openFilter === 'car'" wire:model.live="tableFilters.car.value">
                    <option value="">Any</option>
                    @foreach ($cars as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($pickupValue)]) x-on:click="openFilter = openFilter === 'place' ? null : 'place'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($pickupValue) ? ($locations[$pickupValue] ?? 'Pickup location') : 'Pickup location' }}
                </button>
                <select x-cloak x-show="openFilter === 'place'" wire:model.live="tableFilters.pickup_location.value">
                    <option value="">Any</option>
                    @foreach ($locations as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="tb-chip-menu">
                <button type="button" @class(['tb-chip', 'is-set' => filled($paymentValue)]) x-on:click="openFilter = openFilter === 'pay' ? null : 'pay'">
                    <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                    {{ filled($paymentValue) ? ucfirst($paymentValue) : 'Payment' }}
                </button>
                <select x-cloak x-show="openFilter === 'pay'" wire:model.live="tableFilters.payment.value">
                    <option value="">Any</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="partial">Part paid</option>
                    <option value="paid">Paid in full</option>
                </select>
            </div>
            <div class="tb-toolbar-spacer"></div>
            <div class="tb-m-tools">
                <x-admin.sheet title="Filters">
                    <x-slot:trigger>
                        <button type="button" class="tb-chip">Filters</button>
                    </x-slot:trigger>
                    <input type="search" placeholder="Booking ID" wire:model.live.debounce.300ms="tableFilters.reference.value" aria-label="Booking ID">
                    <input type="search" placeholder="Customer" wire:model.live.debounce.300ms="tableFilters.customer.value" aria-label="Customer">
                    <select wire:model.live="tableFilters.order_status.value" aria-label="Booking status">
                        <option value="">Booking status</option>
                        @foreach (OrderStatus::cases() as $case)
                            <option value="{{ $case->value }}">{{ AdminTableBadgeColors::humanize($case) }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="tableFilters.rental_status.value" aria-label="Rental progress">
                        <option value="">Rental progress</option>
                        @foreach (RentalStatus::cases() as $case)
                            <option value="{{ $case->value }}">{{ AdminTableBadgeColors::humanize($case) }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="tableFilters.car.value" aria-label="Vehicle">
                        <option value="">Vehicle</option>
                        @foreach ($cars as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="tableFilters.pickup_location.value" aria-label="Pickup location">
                        <option value="">Pickup location</option>
                        @foreach ($locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="tableFilters.dropoff_location.value" aria-label="Drop-off location">
                        <option value="">Drop-off location</option>
                        @foreach ($locations as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <input type="date" wire:model.live="tableFilters.pickup_from.date" aria-label="Pickup from">
                    <input type="date" wire:model.live="tableFilters.pickup_until.date" aria-label="Pickup until">
                    <input type="date" wire:model.live="tableFilters.dropoff_from.date" aria-label="Drop-off from">
                    <input type="date" wire:model.live="tableFilters.dropoff_until.date" aria-label="Drop-off until">
                    <input type="date" wire:model.live="tableFilters.booked_from.date" aria-label="Booked from">
                    <input type="date" wire:model.live="tableFilters.booked_until.date" aria-label="Booked until">
                    <select wire:model.live="tableFilters.payment.value" aria-label="Payment">
                        <option value="">Payment</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Part paid</option>
                        <option value="paid">Paid in full</option>
                    </select>
                    <select wire:model.live="tableFilters.source.value" aria-label="Source">
                        <option value="">Source</option>
                        <option value="myterrabook">MyTerraBook</option>
                        <option value="caren">Caren</option>
                        <option value="greenlight">Greenlight</option>
                        <option value="foxie">Foxie</option>
                    </select>
                    <button type="button" onclick="var root=this.closest('[wire\\:id]'); if(root && root.querySelector('.fi-ta-record-checkbox:checked')) Livewire.find(root.getAttribute('wire:id')).mountTableBulkAction('delete')">Delete selected</button>
                </x-admin.sheet>
                <x-admin.sheet title="Sort">
                    <x-slot:trigger>
                        <button type="button" class="tb-chip">Sort</button>
                    </x-slot:trigger>
                    <button type="button" wire:click="$set('tableSort', 'created_at:desc')">Booked, newest</button>
                    <button type="button" wire:click="$set('tableSort', 'created_at:asc')">Booked, oldest</button>
                    <button type="button" wire:click="$set('tableSort', 'pickup_at:asc')">Pickup, soonest</button>
                    <button type="button" wire:click="$set('tableSort', 'pickup_at:desc')">Pickup, latest</button>
                    <button type="button" wire:click="$set('tableSort', 'id:desc')">Reference, newest</button>
                </x-admin.sheet>
            </div>
            <button type="button" class="tb-chip" x-on:click="more = !more">More filters <span class="tb-count">10</span></button>
            <button type="button" class="tb-btn tb-btn-sm" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
                Bulk actions
            </button>
        </div>
        <div class="tb-toolbar tb-more-row" x-cloak x-show="more">
            <input type="search" placeholder="Booking ID" wire:model.live.debounce.300ms="tableFilters.reference.value">
            <input type="search" placeholder="Customer" wire:model.live.debounce.300ms="tableFilters.customer.value">
            <select wire:model.live="tableFilters.order_status.value" aria-label="Booking status">
                <option value="">Booking status</option>
                @foreach (OrderStatus::cases() as $case)
                    <option value="{{ $case->value }}">{{ AdminTableBadgeColors::humanize($case) }}</option>
                @endforeach
            </select>
            <select wire:model.live="tableFilters.rental_status.value" aria-label="Rental progress">
                <option value="">Rental progress</option>
                @foreach (RentalStatus::cases() as $case)
                    <option value="{{ $case->value }}">{{ AdminTableBadgeColors::humanize($case) }}</option>
                @endforeach
            </select>
            <select wire:model.live="tableFilters.dropoff_location.value" aria-label="Drop-off location">
                <option value="">Drop-off location</option>
                @foreach ($locations as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="tableFilters.pickup_until.date" aria-label="Pickup until">
            <input type="date" wire:model.live="tableFilters.dropoff_from.date" aria-label="Drop-off from">
            <input type="date" wire:model.live="tableFilters.dropoff_until.date" aria-label="Drop-off until">
            <input type="date" wire:model.live="tableFilters.booked_from.date" aria-label="Booked from">
            <input type="date" wire:model.live="tableFilters.booked_until.date" aria-label="Booked until">
            <select wire:model.live="tableFilters.source.value" aria-label="Source">
                <option value="">Source</option>
                <option value="myterrabook">MyTerraBook</option>
                <option value="caren">Caren</option>
                <option value="greenlight">Greenlight</option>
                <option value="foxie">Foxie</option>
            </select>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(120px,.8fr) minmax(140px,1fr) minmax(140px,1fr) minmax(160px,1.2fr) 110px 90px 110px 40px">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Reference</span><span>Customer</span><span>Vehicle</span><span>Pickup → Drop-off</span><span>Total</span><span>Payment</span><span>Status</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $paid = (int) $record->payments->sum('amount_cents');
                $total = (int) $record->total_cents;
                $payment = $paid <= 0 ? 'Unpaid' : (($total > 0 && $paid >= $total) ? 'Paid' : 'Deposit');
                $paymentTone = $payment === 'Paid' ? 'ok' : ($payment === 'Deposit' ? 'info' : 'neutral');
                $from = $record->pickup_at;
                $to = $record->dropoff_at;
                $when = ($from && $to) ? ($from->year === $to->year ? $from->format('j M').' → '.$to->format('j M Y') : $from->format('j M Y').' → '.$to->format('j M Y')) : '—';
                $days = ($from && $to) ? max(1, (int) $from->diffInDays($to)) : null;
                $unit = $record->carUnit;
            @endphp
            <div class="tb-row tb-grid" wire:key="order-{{ $key }}" style="grid-template-columns:20px minmax(120px,.8fr) minmax(140px,1fr) minmax(140px,1fr) minmax(160px,1.2fr) 110px 90px 110px 40px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span>{{ $record->reference ?: $record->id }}</span>
                <span class="tb-stack"><strong>{{ $record->customer_name ?: '—' }}</strong><small>{{ $record->customer_email ?: ($record->customer_phone ?: '—') }}</small></span>
                <span class="tb-stack"><strong>{{ $record->car?->name ?: '—' }}</strong><small>Unit {{ max(1, (int) ($unit?->sort_order ?: 1)) }}</small></span>
                <span class="tb-stack"><strong>{{ $when }}</strong><small>{{ $record->pickupLocation?->name ?: '—' }} → {{ $record->dropoffLocation?->name ?: ($record->pickupLocation?->name ?: '—') }}</small></span>
                <span class="tb-stack"><strong>{{ AdminMoney::fromCents($total, (string) ($record->currency ?: 'ISK')) }}</strong>@if ($days)<small>{{ $days }} {{ $days === 1 ? 'day' : 'days' }}</small>@endif</span>
                <span><x-admin.status-pill :tone="$paymentTone">{{ $payment }}</x-admin.status-pill></span>
                <span><x-admin.status-pill :tone="$statusTone($record)">{{ $statusLabel($record) }}</x-admin.status-pill></span>
                <x-admin.row-menu :edit-url="OrderResource::getUrl('edit', ['record' => $record])" />
            </div>
        @empty
            <p class="tb-empty">No reservations match these filters.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" :per-page-options="[25, 50, 100]" />
    </div>
</div>
<x-filament-actions::modals />
