{{-- Daily / hourly / extra-hour fare workbench for ListDailyFares. Expects $fares = ListDailyFares Livewire instance. --}}
@php
    $cars = \App\Models\Car::query()->orderBy('name')->get(['id', 'name', 'main_image_path']);
    $priceTypes = \App\Models\PriceType::query()->orderBy('name')->get(['id', 'name']);
    $tab = $fares->fareTab ?? 'daily';
@endphp

<div class="fi-section ir-fares-workbench" data-fare-workbench="1">
    <div class="ir-fares-tabs" role="tablist" aria-label="{{ __('Fare type') }}">
        <button type="button" role="tab" wire:click="$set('fareTab', 'daily')" aria-selected="{{ $tab === 'daily' ? 'true' : 'false' }}" class="ir-fares-tab @if($tab === 'daily') is-active @endif">
            {{ __('Daily fares') }}
        </button>
        <button type="button" role="tab" wire:click="$set('fareTab', 'extra_hours')" aria-selected="{{ $tab === 'extra_hours' ? 'true' : 'false' }}" class="ir-fares-tab @if($tab === 'extra_hours') is-active @endif">
            {{ __('Extra hours charges') }}
        </button>
        <button type="button" role="tab" wire:click="$set('fareTab', 'hourly')" aria-selected="{{ $tab === 'hourly' ? 'true' : 'false' }}" class="ir-fares-tab @if($tab === 'hourly') is-active @endif">
            {{ __('Hourly fares') }}
        </button>
    </div>

    <div class="ir-fares-grid">
        <section class="ir-fares-panel ir-fares-panel--left" aria-label="{{ __('Fare setup') }}">
            <div class="ir-fares-vehicle-strip">
                <div class="ir-fares-car-image">
                    @php $activeCar = $cars->firstWhere('id', $fares->benchCarId); @endphp
                    @if ($activeCar && $activeCar->main_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($activeCar->main_image_path) }}" alt="" />
                    @else
                        <span>{{ __('No image') }}</span>
                    @endif
                </div>
                <div class="ir-fares-vehicle-fields">
                    <label class="ir-fares-label" for="bench-car-select">{{ __('Vehicle') }}</label>
                    <select id="bench-car-select" wire:model.live="benchCarId" class="ir-fares-select">
                        @forelse ($cars as $car)
                            <option value="{{ $car->id }}">{{ $car->name }}</option>
                        @empty
                            <option value="">{{ __('No vehicles') }}</option>
                        @endforelse
                    </select>

                    <label class="ir-fares-label" for="bench-price-type-select">{{ __('Price type') }}</label>
                    <select id="bench-price-type-select" wire:model.live="benchPriceTypeId" class="ir-fares-select">
                        @forelse ($priceTypes as $pt)
                            <option value="{{ $pt->id }}">{{ $pt->name }}</option>
                        @empty
                            <option value="">{{ __('No price types') }}</option>
                        @endforelse
                    </select>
                </div>
            </div>

            @if ($tab === 'daily')
                <form wire:submit="insertFareBand" class="ir-fares-editor">
                    <h3 class="ir-fares-editor-title">{{ __('Daily fares input') }}</h3>
                    <div class="ir-fares-fields-two">
                        <div>
                            <label class="ir-fares-label">{{ __('Days — from') }}</label>
                            <input type="number" min="1" wire:model="insertFromDays" class="ir-fares-input" />
                        </div>
                        <div>
                            <label class="ir-fares-label">{{ __('Days — to') }}</label>
                            <input type="number" min="1" wire:model="insertToDays" class="ir-fares-input" />
                        </div>
                    </div>
                    <div>
                        <label class="ir-fares-label">{{ __('Daily price (ISK per day)') }}</label>
                        <div class="ir-fares-price-wrap">
                            <input type="text" inputmode="decimal" wire:model="insertPriceIsk" placeholder="9800" class="ir-fares-input" />
                            <span class="ir-fares-currency">ISK</span>
                        </div>
                    </div>
                    <button type="submit" class="ir-fares-primary-btn">{{ __('Insert') }}</button>
                </form>
            @elseif ($tab === 'hourly')
                <form wire:submit="insertHourlyFareBand" class="ir-fares-editor">
                    <h3 class="ir-fares-editor-title">{{ __('Hourly fares input') }}</h3>
                    <p class="ir-fares-help">{{ __('Rental length under 24 hours: define a window in whole hours and the total ISK for that window.') }}</p>
                    <div class="ir-fares-fields-two">
                        <div>
                            <label class="ir-fares-label">{{ __('Hours — from') }}</label>
                            <input type="number" min="1" max="72" wire:model="insertHourlyFromHours" class="ir-fares-input" />
                        </div>
                        <div>
                            <label class="ir-fares-label">{{ __('Hours — to') }}</label>
                            <input type="number" min="1" max="72" wire:model="insertHourlyToHours" class="ir-fares-input" />
                        </div>
                    </div>
                    <div>
                        <label class="ir-fares-label">{{ __('Total price (ISK for this duration window)') }}</label>
                        <div class="ir-fares-price-wrap">
                            <input type="text" inputmode="decimal" wire:model="insertHourlyTotalIsk" placeholder="12000" class="ir-fares-input" />
                            <span class="ir-fares-currency">ISK</span>
                        </div>
                    </div>
                    <button type="submit" class="ir-fares-primary-btn">{{ __('Insert') }}</button>
                </form>
            @else
                <form wire:submit="saveExtraHourFare" class="ir-fares-editor">
                    <h3 class="ir-fares-editor-title">{{ __('Extra hours charge') }}</h3>
                    <p class="ir-fares-help">{{ __('For daily rentals that exceed full days, each billable extra hour uses this additional rate.') }}</p>
                    <div>
                        <label class="ir-fares-label">{{ __('Charge per extra hour (ISK)') }}</label>
                        <div class="ir-fares-price-wrap">
                            <input type="text" inputmode="decimal" wire:model="extraHourChargeIsk" placeholder="2500" class="ir-fares-input" />
                            <span class="ir-fares-currency">ISK</span>
                        </div>
                    </div>
                    <button type="submit" class="ir-fares-primary-btn">{{ __('Save') }}</button>
                </form>
            @endif
        </section>

        <section class="ir-fares-panel ir-fares-panel--right" aria-label="{{ __('Fare table') }}">
            <div class="ir-fares-table-head">
                <h3 class="ir-fares-table-title">
                    @if ($tab === 'extra_hours')
                        {{ __('Extra hour preview') }}
                    @else
                        {{ __('Fares table') }}
                    @endif
                </h3>
                @if ($tab === 'daily' || $tab === 'hourly')
                    <button type="button" wire:click="openUpdateFaresModal" class="ir-fares-secondary-btn">{{ __('Update fares') }}</button>
                @endif
            </div>

            <div class="ir-fares-table-wrap">
                <table class="ir-fares-table">
                    @if ($tab === 'daily')
                        <thead>
                            <tr>
                                <th class="w-check"></th>
                                <th>{{ __('Fare for days') }}</th>
                                <th class="text-right">{{ __('Price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fares->benchDayRows as $row)
                                <tr wire:key="fare-day-{{ $row['day'] }}">
                                    <td><input type="checkbox" wire:model.live="selectedBenchDays" value="{{ $row['day'] }}" /></td>
                                    <td>{{ $row['day'] }}</td>
                                    <td class="text-right">{{ number_format($row['total_cents'] / 100, 2, ',', '.') }} ISK</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">{{ __('Select a vehicle and price type.') }}</td></tr>
                            @endforelse
                        </tbody>
                    @elseif ($tab === 'hourly')
                        <thead>
                            <tr>
                                <th class="w-check"></th>
                                <th>{{ __('Rental length (hours)') }}</th>
                                <th class="text-right">{{ __('Price') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fares->benchHourRows as $row)
                                <tr wire:key="fare-hour-{{ $row['hour'] }}">
                                    <td><input type="checkbox" wire:model.live="selectedBenchHours" value="{{ $row['hour'] }}" /></td>
                                    <td>{{ $row['hour'] }}</td>
                                    <td class="text-right">{{ number_format($row['total_cents'] / 100, 2, ',', '.') }} ISK</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">{{ __('Select a vehicle and price type.') }}</td></tr>
                            @endforelse
                        </tbody>
                    @else
                        <thead>
                            <tr>
                                <th>{{ __('Extra hours') }}</th>
                                <th class="text-right">{{ __('Surcharge') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fares->benchExtraHourRows as $row)
                                <tr wire:key="fare-ex-{{ $row['extra_hours'] }}">
                                    <td>{{ $row['extra_hours'] }}</td>
                                    <td class="text-right">{{ number_format($row['total_cents'] / 100, 2, ',', '.') }} ISK</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center">{{ __('Select a vehicle and price type.') }}</td></tr>
                            @endforelse
                        </tbody>
                    @endif
                </table>
            </div>

            @if ($tab === 'daily' && $fares->benchDayTotalPages > 1)
                <div class="ir-fares-pagination">
                    <span>{{ $fares->benchDayTotal }} {{ __('items') }}</span>
                    <div class="ir-fares-pagination-actions">
                        <button type="button" wire:click="setBenchDayPage({{ max(1, $fares->benchDayPage - 1) }})" @if($fares->benchDayPage <= 1) disabled @endif>{{ __('Previous') }}</button>
                        <span>{{ __('Page') }} {{ $fares->benchDayPage }} {{ __('of') }} {{ $fares->benchDayTotalPages }}</span>
                        <button type="button" wire:click="setBenchDayPage({{ min($fares->benchDayTotalPages, $fares->benchDayPage + 1) }})" @if($fares->benchDayPage >= $fares->benchDayTotalPages) disabled @endif>{{ __('Next') }}</button>
                    </div>
                </div>
            @endif
            @if ($tab === 'hourly' && $fares->benchHourTotalPages > 1)
                <div class="ir-fares-pagination">
                    <span>{{ $fares->benchHourTotal }} {{ __('items') }}</span>
                    <div class="ir-fares-pagination-actions">
                        <button type="button" wire:click="setBenchHourPage({{ max(1, $fares->benchHourPage - 1) }})" @if($fares->benchHourPage <= 1) disabled @endif>{{ __('Previous') }}</button>
                        <span>{{ __('Page') }} {{ $fares->benchHourPage }} {{ __('of') }} {{ $fares->benchHourTotalPages }}</span>
                        <button type="button" wire:click="setBenchHourPage({{ min($fares->benchHourTotalPages, $fares->benchHourPage + 1) }})" @if($fares->benchHourPage >= $fares->benchHourTotalPages) disabled @endif>{{ __('Next') }}</button>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>

@if ($fares->showUpdateFaresModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-black/40" wire:click="closeUpdateFaresModal"></div>
        <div class="relative z-10 w-full max-w-md rounded-xl bg-white dark:bg-gray-900 shadow-xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ __('Update fares') }}</h2>
            @if (($fares->fareTab ?? 'daily') === 'hourly')
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Set the total ISK for the rental duration window that spans the lowest and highest selected hour rows. One hourly band will be created.') }}</p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Total price (ISK)') }}</label>
                    <input type="text" inputmode="decimal" wire:model="bulkUpdatePriceIsk" class="fi-input block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-gray-950/10" />
                </div>
            @else
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Set a new ISK price per day for the selected day rows. A single fare band will be created from the lowest to the highest selected day.') }}</p>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('New daily price (ISK)') }}</label>
                    <input type="text" inputmode="decimal" wire:model="bulkUpdatePriceIsk" class="fi-input block w-full rounded-lg border-0 bg-white px-3 py-2 text-sm shadow-sm ring-1 ring-gray-950/10" />
                </div>
            @endif
            <div class="flex justify-end gap-2">
                <button type="button" wire:click="closeUpdateFaresModal" class="fi-btn fi-btn-size-sm fi-btn-variant-outline px-3 py-2 rounded-lg">{{ __('Cancel') }}</button>
                <button type="button" wire:click="applyBulkFareUpdateFromModal" class="fi-btn fi-btn-size-sm fi-btn-color-primary fi-btn-variant-filled px-3 py-2 rounded-lg">{{ __('Apply') }}</button>
            </div>
        </div>
    </div>
@endif
