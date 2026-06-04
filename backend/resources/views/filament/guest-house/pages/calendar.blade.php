<x-filament-panels::page>
    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
        @forelse ($this->rows as $row)
            <div class="border-b border-gray-100 p-4 dark:border-gray-800">
                <h3 class="mb-2 text-sm font-semibold">{{ $row['house'] }}</h3>
                <div class="flex flex-wrap gap-1">
                    @foreach ($row['cells'] as $cell)
                        <div
                            class="flex h-10 w-10 flex-col items-center justify-center rounded text-xs {{ $cell['bookings'] > 0 ? 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-100' : 'bg-gray-50 text-gray-500 dark:bg-gray-800' }}"
                            title="{{ $cell['date'] }} — {{ $cell['bookings'] }} booking(s)"
                        >
                            <span>{{ $cell['day'] }}</span>
                            @if ($cell['bookings'] > 0)
                                <span class="text-[10px] font-bold">{{ $cell['bookings'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="p-6 text-sm text-gray-500">No guest houses configured yet.</p>
        @endforelse
    </div>
</x-filament-panels::page>
