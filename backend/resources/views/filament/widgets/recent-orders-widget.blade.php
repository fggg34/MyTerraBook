@php
    $pollingInterval = $this->getPollingInterval();
@endphp

<x-filament-widgets::widget
    class="fi-wi-table"
    :attributes="
        (new \Illuminate\View\ComponentAttributeBag)
            ->merge([
                'wire:poll.' . $pollingInterval => $pollingInterval ? true : null,
            ], escape: false)
    "
>
    {{ $this->table }}
</x-filament-widgets::widget>
