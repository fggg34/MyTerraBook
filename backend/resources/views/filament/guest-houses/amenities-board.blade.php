@php
    $search = trim((string) (method_exists($this, 'getTableSearch') ? $this->getTableSearch() : ''));
    $amenityGroups = \App\Models\GuestHouseAmenity::query()
        ->when($search !== '', fn ($query) => $query->where(function ($inner) use ($search): void {
            $inner->where('name', 'like', '%'.$search.'%')->orWhere('group', 'like', '%'.$search.'%');
        }))
        ->orderBy('group')
        ->orderBy('name')
        ->get()
        ->groupBy(fn (\App\Models\GuestHouseAmenity $amenity): string => $amenity->group ?: 'General');
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
<div class="tb-amenity-board" id="tb-amenity-board" data-wire-id="{{ $this->getId() }}">
    @forelse ($amenityGroups as $group => $items)
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
                        <button type="button" class="tb-row-btn" wire:click="mountTableAction('edit', '{{ $amenity->id }}')" aria-label="Edit {{ $amenity->name }}">
                            <img src="{{ asset('admin-redesign/icon-tb-pencil.svg') }}" alt="" width="16" height="16">
                        </button>
                        @php $deleteItem = [['label' => 'Delete…', 'wire' => "mountTableAction('delete', '{$amenity->id}')", 'danger' => true]]; @endphp
                        <x-admin.row-menu :items="$deleteItem" />
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="tb-side-muted">No amenities yet. Use New amenity to add the first one.</p>
    @endforelse
</div>
<x-filament-actions::modals />
