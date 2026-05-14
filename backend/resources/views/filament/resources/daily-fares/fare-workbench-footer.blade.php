{{-- Footer summary for hourly / extra-hour tabs on ListDailyFares. Expects $fares. --}}
@php
    $tab = $fares->fareTab ?? 'daily';
@endphp

@if ($tab === 'hourly')
    <div class="fi-section rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm mb-6 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-white/10 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ __('Configured hourly bands') }}</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ __('Bands for the vehicle and price type selected above. Use Edit for fine-grained changes.') }}</p>
        </div>
        <div class="overflow-x-auto p-4 pt-0">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/80">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200">{{ __('Min minutes') }}</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200">{{ __('Max minutes') }}</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-200">{{ __('Total (ISK)') }}</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-200 w-24"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse ($fares->hourlyBandRecords as $band)
                        <tr wire:key="hourly-band-{{ $band->id }}">
                            <td class="px-3 py-2 tabular-nums">{{ $band->min_minutes }}</td>
                            <td class="px-3 py-2 tabular-nums">{{ $band->max_minutes }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ number_format($band->total_price_cents / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ \App\Filament\Resources\HourlyFares\HourlyFareResource::getUrl('edit', ['record' => $band]) }}" wire:navigate class="text-primary-600 hover:underline text-sm font-medium">
                                    {{ __('Edit') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-gray-500">{{ __('No hourly bands yet. Use Insert above.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@elseif ($tab === 'extra_hours')
    <div class="fi-section rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm mb-6 overflow-hidden">
        <div class="border-b border-gray-200 dark:border-white/10 px-4 py-3">
            <h3 class="text-sm font-semibold text-gray-950 dark:text-white">{{ __('Saved extra-hour charge') }}</h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ __('One rate per vehicle and price type. Update it with Save in the workbench.') }}</p>
        </div>
        <div class="overflow-x-auto p-4 pt-0">
            @php $extra = $fares->extraHourBandRecord; @endphp
            @if ($extra)
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800/80">
                        <tr>
                            <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-200">{{ __('Per extra hour (ISK)') }}</th>
                            <th class="px-3 py-2 text-right font-medium text-gray-700 dark:text-gray-200 w-24"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="px-3 py-2 tabular-nums">{{ number_format($extra->charge_per_extra_hour_cents / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ \App\Filament\Resources\ExtraHourFares\ExtraHourFareResource::getUrl('edit', ['record' => $extra]) }}" wire:navigate class="text-primary-600 hover:underline text-sm font-medium">
                                    {{ __('Edit') }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            @else
                <p class="text-sm text-gray-500 py-4">{{ __('No extra-hour charge saved yet. Enter a rate and click Save above.') }}</p>
            @endif
        </div>
    </div>
@endif
