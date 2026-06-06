<x-filament-panels::page>
    <div class="space-y-8">
        <section>
            <h2 class="mb-3 text-lg font-semibold text-gray-950 dark:text-white">
                Guesthouses awaiting approval ({{ $pendingGuestHouses->count() }})
            </h2>

            @if ($pendingGuestHouses->isEmpty())
                <p class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-5 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400">
                    No guesthouses are waiting for review.
                </p>
            @else
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Host</th>
                                <th class="px-4 py-3">Location</th>
                                <th class="px-4 py-3">Details</th>
                                <th class="px-4 py-3">Submitted</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($pendingGuestHouses as $house)
                                <tr wire:key="gh-{{ $house->id }}" class="text-gray-950 dark:text-gray-100">
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-semibold text-gray-950 dark:text-white">{{ $house->name }}</div>
                                        <span class="mt-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-900 dark:bg-amber-500/20 dark:text-amber-200">
                                            pending review
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $house->host?->name ?? '—' }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">{{ $house->host?->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                        {{ $house->city }}{{ $house->country ? ', '.$house->country : '' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                        <div>{{ ucfirst($house->type?->value ?? '—') }}</div>
                                        <div>{{ $house->max_guests }} guests · {{ $house->bedrooms }} bed · €{{ number_format($house->base_price_per_night / 100, 2) }}/night</div>
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                        {{ $house->submitted_at?->format('d M Y H:i') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="flex flex-wrap gap-2">
                                            <x-filament::button size="sm" color="success" wire:click="approveGuestHouse({{ $house->id }})">Approve</x-filament::button>
                                            <x-filament::button size="sm" color="warning" wire:click="mountAction('requestGuestHouseChanges', { guestHouseId: {{ $house->id }} })">Request changes</x-filament::button>
                                            <x-filament::button size="sm" color="danger" wire:click="mountAction('rejectGuestHouse', { guestHouseId: {{ $house->id }} })">Reject</x-filament::button>
                                            <x-filament::button size="sm" color="gray" tag="a" href="{{ \App\Filament\GuestHouse\Resources\GuestHouseResource::getUrl('edit', ['record' => $house]) }}">Edit</x-filament::button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold text-gray-950 dark:text-white">
                Vehicles awaiting approval ({{ $pendingCars->count() }})
            </h2>

            @if ($pendingCars->isEmpty())
                <p class="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-5 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400">
                    No vehicles are waiting for review.
                </p>
            @else
                <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Host</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Details</th>
                                <th class="px-4 py-3">Submitted</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($pendingCars as $car)
                                <tr wire:key="car-{{ $car->id }}" class="text-gray-950 dark:text-gray-100">
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-semibold text-gray-950 dark:text-white">{{ $car->name }}</div>
                                        <span class="mt-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-900 dark:bg-amber-500/20 dark:text-amber-200">
                                            pending review
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="font-medium text-gray-950 dark:text-white">{{ $car->host?->name ?? '—' }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">{{ $car->host?->email }}</div>
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                        {{ $car->category?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                        <div>{{ ucfirst($car->transmission ?? '—') }} · {{ ucfirst($car->fuel_type ?? '—') }}</div>
                                        <div>{{ $car->units_available }} unit(s)</div>
                                    </td>
                                    <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                        {{ $car->submitted_at?->format('d M Y H:i') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <div class="flex flex-wrap gap-2">
                                            <x-filament::button size="sm" color="success" wire:click="approveCar({{ $car->id }})">Approve</x-filament::button>
                                            <x-filament::button size="sm" color="warning" wire:click="mountAction('requestCarChanges', { carId: {{ $car->id }} })">Request changes</x-filament::button>
                                            <x-filament::button size="sm" color="danger" wire:click="mountAction('rejectCar', { carId: {{ $car->id }} })">Reject</x-filament::button>
                                            <x-filament::button size="sm" color="gray" tag="a" href="{{ \App\Filament\Resources\Cars\CarResource::getUrl('edit', ['record' => $car]) }}">Edit</x-filament::button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
