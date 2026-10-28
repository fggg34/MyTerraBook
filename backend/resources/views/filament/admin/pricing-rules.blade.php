@php
    use App\Filament\Resources\SpecialPrices\SpecialPriceResource;
    use App\Models\SpecialPrice;
    use App\Support\AdminMoney;
    use App\Support\AdminTableBadgeColors;
    use Illuminate\Support\Carbon;

    $records = $getRecords();
    $pageName = $this->getTablePaginationPageName();
    $rules = SpecialPrice::query()->whereNotNull('date_from')->whereNotNull('date_to')->orderBy('date_from')->get();
    $overlapIds = [];
    foreach ($rules as $index => $left) {
        foreach ($rules as $inner => $right) {
            if ($inner <= $index) {
                continue;
            }
            if ($left->date_from <= $right->date_to && $right->date_from <= $left->date_to) {
                $overlapIds[$left->id] = true;
                $overlapIds[$right->id] = true;
            }
        }
    }
    $spanStart = $rules->min('date_from');
    $spanEnd = $rules->max('date_to');
    $months = [];
    if ($spanStart && $spanEnd) {
        $cursor = Carbon::parse($spanStart)->startOfMonth();
        $last = Carbon::parse($spanEnd)->startOfMonth();
        while ($cursor <= $last && count($months) < 12) {
            $months[] = $cursor->copy();
            $cursor->addMonth();
        }
    }
    $spanDays = ($spanStart && $spanEnd) ? max(1, Carbon::parse($spanStart)->startOfMonth()->diffInDays(Carbon::parse($spanEnd)->endOfMonth())) : 1;
@endphp

<div class="tb-list" x-data="{ onlyOverlaps: false }">
    @if ($overlapIds !== [])
        <div class="tb-banner">
            <span>{{ count($overlapIds) }} {{ count($overlapIds) === 1 ? 'rule overlaps' : 'rules overlap' }} on the same dates.</span>
            <button type="button" x-on:click="onlyOverlaps = !onlyOverlaps" x-text="onlyOverlaps ? 'Show all' : 'Show overlaps'"></button>
        </div>
    @endif
    @if ($months !== [])
        <section class="tb-cal-card">
            <h2>Rules on the calendar</h2>
            <div class="tb-cal-legend"><span class="is-charge"></span> Charge <span class="is-discount"></span> Discount</div>
            <div class="tb-cal-months">
                @foreach ($months as $month)
                    <span>{{ $month->format('M Y') }}</span>
                @endforeach
            </div>
            @foreach ($rules as $rule)
                @php
                    $left = (Carbon::parse($spanStart)->startOfMonth()->diffInDays($rule->date_from) / $spanDays) * 100;
                    $width = (max(1, $rule->date_from->diffInDays($rule->date_to) + 1) / $spanDays) * 100;
                    $discount = str_contains(strtolower((string) $rule->type), 'discount');
                @endphp
                <div class="tb-cal-row">
                    <span>{{ $rule->name }}</span>
                    <div><i @class(['is-discount' => $discount, 'is-charge' => ! $discount]) style="left: {{ round($left, 2) }}%; width: {{ round(min(100 - $left, $width), 2) }}%"></i></div>
                </div>
            @endforeach
        </section>
    @endif
    <div class="tb-table-card" id="rules-table" x-data="filamentTable({ areGroupsCollapsedByDefault: false, canTrackDeselectedRecords: false, currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()), maxSelectableRecords: null, selectsCurrentPageOnly: true, $wire })">
        <input type="hidden" value="{{ method_exists($records, 'total') ? $records->total() : $records->count() }}" x-ref="allSelectableRecordsCount">
        <div class="tb-toolbar">
            <label class="tb-search">
                <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
                <input type="search" placeholder="Search rule…" wire:model.live.debounce.300ms="tableSearch">
            </label>
            <div class="tb-toolbar-spacer"></div>
            <button type="button" class="tb-icon-btn" aria-label="Delete selected" x-on:click="getSelectedRecordsCount() && $wire.mountTableBulkAction('delete')">
                <img src="{{ asset('admin-redesign/icon-tb-bulk.svg') }}" alt="" width="16" height="16">
            </button>
        </div>
        <div class="tb-head tb-grid" style="grid-template-columns:20px minmax(160px,1.3fr) minmax(150px,1.1fr) 100px 110px minmax(120px,1fr) 56px">
            <span><input class="tb-check fi-ta-page-checkbox" type="checkbox" aria-label="Select page" x-on:click="toggleSelectRecordsOnPage"></span>
            <span>Name</span><span>Dates</span><span>Type</span><span>Value</span><span>Applies to</span><span></span>
        </div>
        @forelse ($records as $record)
            @php
                $key = $getRecordKey($record);
                $percentMode = str_contains(strtolower((string) $record->value_mode), 'percent');
                $amount = $percentMode
                    ? rtrim(rtrim(number_format(((int) $record->value_percent_bips) / 100, 2, '.', ''), '0'), '.').'%'
                    : AdminMoney::fromCents((int) $record->value_fixed_cents);
                $discount = str_contains(strtolower((string) $record->type), 'discount');
                $ids = array_filter((array) $record->vehicle_ids);
                $applies = $ids === [] ? 'All vehicles' : count($ids).' '.(count($ids) === 1 ? 'vehicle' : 'vehicles');
                $overlaps = isset($overlapIds[$record->id]);
            @endphp
            <div class="tb-row tb-grid" wire:key="rule-{{ $key }}" x-show="!onlyOverlaps || {{ $overlaps ? 'true' : 'false' }}" style="grid-template-columns:20px minmax(160px,1.3fr) minmax(150px,1.1fr) 100px 110px minmax(120px,1fr) 56px">
                <span><input class="tb-check fi-ta-record-checkbox" type="checkbox" value="{{ $key }}" x-on:click="toggleSelectedRecord(@js($key))" x-bind:checked="isRecordSelected(@js($key)) ? 'checked' : null"></span>
                <span class="tb-stack">
                    <strong>
                        @if ($overlaps)<span class="tb-warn-text">!</span> @endif
                        {{ $record->name }}
                    </strong>
                </span>
                <span>{{ $record->date_from?->format('j M Y') }} – {{ $record->date_to?->format('j M Y') }}</span>
                <span><x-admin.status-pill :tone="$discount ? 'ok' : 'info'">{{ AdminTableBadgeColors::humanize((string) $record->type) }}</x-admin.status-pill></span>
                <span>{{ ($discount ? '−' : '+').$amount }}</span>
                <span>{{ $applies }}</span>
                <x-admin.row-menu :edit-url="SpecialPriceResource::getUrl('edit', ['record' => $record])" :items="[]" />
            </div>
        @empty
            <p class="tb-empty">No pricing rules match this search.</p>
        @endforelse
        <x-admin.table-footer :records="$records" :page-name="$pageName" />
    </div>
</div>
<x-filament-actions::modals />
