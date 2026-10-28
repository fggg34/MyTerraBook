@php
    $path = (string) ($car->main_image_path ?? '');
    $photo = null;
    if ($path !== '') {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $photo = $path;
        } elseif (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            $photo = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        }
    }
    $category = trim(implode(' · ', array_filter([
        $car->subCategory?->mainCategory?->name,
        $car->subCategory?->name,
    ])));
    $drive = $car->drive_type instanceof \App\Enums\DriveType
        ? $car->drive_type->label()
        : (string) ($car->drive_type ?? '');
    $specs = implode(' · ', array_filter([
        $drive,
        filled($car->transmission) ? ucfirst((string) $car->transmission) : null,
        filled($car->fuel_type) ? ucfirst((string) $car->fuel_type) : null,
    ]));
    $units = max(1, (int) ($car->units_available ?? 1));
    $host = $car->host?->name
        ?: (filled($car->external_provider) ? ucfirst((string) $car->external_provider) : 'MyTerraBook');
    $fare = $car->dailyFares
        ->sortBy('from_days')
        ->first(fn ($row) => (int) $row->from_days <= 1 && (int) $row->to_days >= 1)
        ?? $car->dailyFares->sortBy('from_days')->first();
    $cents = (int) ($fare?->price_per_day_cents ?? 0);
    $price = $cents > 0
        ? 'ISK '.(preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', (string) intdiv($cents, 100)) ?? (string) intdiv($cents, 100))
        : 'Not set';
    $editUrl = \App\Filament\Resources\Cars\CarResource::getUrl('edit', ['record' => $car]);
@endphp
<a class="tb-vehicle-card" href="{{ $editUrl }}">
    <div class="tb-vehicle-photo">
        @if ($photo)
            <img src="{{ $photo }}" alt="">
        @else
            <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <path d="M8 30h32l-3.5-9.5A4 4 0 0 0 32.8 18H15.2a4 4 0 0 0-3.7 2.5L8 30Z" stroke="currentColor" stroke-width="2"/>
                <circle cx="16" cy="32" r="3.5" stroke="currentColor" stroke-width="2"/>
                <circle cx="32" cy="32" r="3.5" stroke="currentColor" stroke-width="2"/>
            </svg>
        @endif
    </div>
    <div class="tb-vehicle-body">
        <div class="tb-vehicle-pills">
            <x-admin.status-pill :tone="$car->is_active ? 'ok' : 'bad'">{{ $car->is_active ? 'Active' : 'Inactive' }}</x-admin.status-pill>
            @if ($car->listing_status === \App\Enums\ListingApprovalStatus::PendingReview)
                <x-admin.status-pill tone="warn">Pending review</x-admin.status-pill>
            @endif
            @if (! $photo)
                <x-admin.status-pill tone="warn">No photo</x-admin.status-pill>
            @endif
        </div>
        <h3 class="tb-vehicle-name">{{ $car->name }}</h3>
        @if ($category !== '')
            <p class="tb-vehicle-meta">{{ $category }}</p>
        @endif
        @if ($specs !== '')
            <p class="tb-vehicle-meta">{{ $specs }}</p>
        @endif
        <div class="tb-vehicle-foot">
            <div>
                <strong>{{ $host }}</strong>
                <small>{{ $units }} {{ $units === 1 ? 'unit' : 'units' }}@if ($car->year) · {{ $car->year }}@endif</small>
            </div>
            <div>
                <small>Base price</small>
                <strong @class(['is-missing' => $cents <= 0])>{{ $price }}</strong>
            </div>
        </div>
    </div>
</a>
