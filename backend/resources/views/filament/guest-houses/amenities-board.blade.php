@php
    /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\GuestHouseAmenity>> $groups */
    $groupTitle = static function (string $group): string {
        return match (strtolower($group)) {
            'general' => 'General',
            'bathroom', 'bathroom_laundry', 'bathroom & laundry' => 'Bathroom & laundry',
            'kitchen' => 'Kitchen',
            'outdoor', 'outdoor_wellness', 'outdoor & wellness' => 'Outdoor & wellness',
            default => \Illuminate\Support\Str::headline($group),
        };
    };
@endphp
<div class="tb-amenity-board">
    @forelse ($groups as $group => $items)
        <section class="tb-amenity-group">
            <header>
                <h2>{{ $groupTitle((string) $group) }}</h2>
                <span>{{ $items->count() }}</span>
            </header>
            <ul>
                @foreach ($items as $amenity)
                    <li>
                        <span class="tb-amenity-icon">
                            {!! \App\Support\IconCatalog::tableIconHtml($amenity->icon) ?: '' !!}
                        </span>
                        <span class="tb-amenity-name">{{ $amenity->name }}</span>
                        <button type="button" class="tb-icon-btn" wire:click="mountTableAction('edit', {{ $amenity->id }})" aria-label="Edit {{ $amenity->name }}">Edit</button>
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="tb-side-muted">No amenities yet. Use New amenity to add the first one.</p>
    @endforelse
</div>
