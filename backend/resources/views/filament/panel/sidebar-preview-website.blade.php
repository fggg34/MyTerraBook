@php
    $frontendUrl = config('app.frontend_url', '/');
@endphp

<div class="fi-sidebar-preview-website mx-4 mb-3">
    <ul class="fi-sidebar-nav-groups">
        <x-filament-panels::sidebar.item
            :icon="\Filament\Support\Icons\Heroicon::OutlinedGlobeAlt"
            :url="$frontendUrl"
            :should-open-url-in-new-tab="true"
        >
            Preview website
        </x-filament-panels::sidebar.item>
    </ul>
</div>
