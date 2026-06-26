<x-filament-panels::page>
    <style>
        .gh-cal-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 0.75rem;
            border: 1px solid rgb(229 231 235);
            background: #fff;
        }

        .dark .gh-cal-wrap {
            border-color: rgb(55 65 81);
            background: rgb(17 24 39);
        }

        .gh-cal-house {
            padding: 1rem;
            border-bottom: 1px solid rgb(243 244 246);
        }

        .dark .gh-cal-house {
            border-bottom-color: rgb(31 41 55);
        }

        .gh-cal-house:last-child {
            border-bottom: none;
        }

        .gh-cal-house h3 {
            margin: 0 0 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(31 41 55);
        }

        .dark .gh-cal-house h3 {
            color: rgb(243 244 246);
        }

        .gh-cal-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(2.75rem, 1fr));
            gap: 0.35rem;
            min-width: min(100%, 22rem);
        }

        .gh-cal-cell {
            display: flex;
            min-height: 2.75rem;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            line-height: 1.1;
            background: rgb(249 250 251);
            color: rgb(107 114 128);
        }

        .gh-cal-cell.is-booked {
            background: rgb(51 78 104 / 0.12);
            color: rgb(36 59 83);
            font-weight: 600;
        }

        .dark .gh-cal-cell {
            background: rgb(31 41 55);
            color: rgb(156 163 175);
        }

        .dark .gh-cal-cell.is-booked {
            background: rgb(51 78 104 / 0.35);
            color: rgb(241 245 249);
        }

        .gh-cal-cell__count {
            margin-top: 0.1rem;
            font-size: 0.625rem;
            font-weight: 700;
        }

        @media (max-width: 640px) {
            .gh-cal-house {
                padding: 0.85rem;
            }

            .gh-cal-grid {
                grid-template-columns: repeat(7, minmax(2.5rem, 1fr));
                gap: 0.25rem;
            }

            .gh-cal-cell {
                min-height: 2.5rem;
                font-size: 0.6875rem;
            }
        }
    </style>

    <div class="gh-cal-wrap">
        @forelse ($this->rows as $row)
            <div class="gh-cal-house">
                <h3>{{ $row['house'] }}</h3>
                <div class="gh-cal-grid">
                    @foreach ($row['cells'] as $cell)
                        <div
                            class="gh-cal-cell {{ $cell['bookings'] > 0 ? 'is-booked' : '' }}"
                            title="{{ $cell['date'] }}, {{ $cell['bookings'] }} booking(s)"
                        >
                            <span>{{ $cell['day'] }}</span>
                            @if ($cell['bookings'] > 0)
                                <span class="gh-cal-cell__count">{{ $cell['bookings'] }}</span>
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
