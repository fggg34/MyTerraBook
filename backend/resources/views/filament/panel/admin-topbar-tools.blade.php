@if (auth()->check())
@php
    $comingSoon = false;
    try {
        $comingSoon = app(\App\Services\SitePreviewService::class)->isComingSoonEnabled();
    } catch (\Throwable) {
        $comingSoon = false;
    }

    $create = function (string $class, string $label): ?array {
        try {
            if (! $class::canCreate()) {
                return null;
            }

            return ['label' => $label, 'url' => $class::getUrl('create')];
        } catch (\Throwable) {
            return null;
        }
    };

    $creates = array_values(array_filter([
        $create(\App\Filament\Resources\Orders\OrderResource::class, 'Car reservation'),
        $create(\App\Filament\GuestHouse\Resources\GuestHouseBookingResource::class, 'Guest house booking'),
        $create(\App\Filament\Resources\Cars\CarResource::class, 'Vehicle'),
        $create(\App\Filament\GuestHouse\Resources\GuestHouseResource::class, 'Guest house'),
        $create(\App\Filament\Resources\BlogPosts\BlogPostResource::class, 'Good to Know post'),
    ]));
@endphp

@if ($comingSoon)
    <div class="tb-status">
        <img src="{{ asset('admin-redesign/icon-status-dot.svg') }}" alt="" width="7" height="7">
        <span>Coming Soon mode is on</span>
    </div>
@endif

@if ($creates !== [])
    <details class="tb-new">
        <summary>
            <img src="{{ asset('admin-redesign/icon-plus.svg') }}" alt="" width="16" height="16">
            New
        </summary>
        <div>
            @foreach ($creates as $item)
                <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @endforeach
        </div>
    </details>
@endif

<span class="tb-bell" aria-hidden="true">
    <img src="{{ asset('admin-redesign/icon-bell.svg') }}" alt="" width="36" height="36">
</span>
@endif
