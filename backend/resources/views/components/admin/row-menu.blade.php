@props(['editUrl' => null, 'items' => [], 'icon' => 'icon-tb-pencil.svg'])

<span class="tb-actions tb-keep">
    @if (filled($editUrl))
        <a class="tb-row-btn" href="{{ $editUrl }}" aria-label="{{ str_contains($icon, 'eye') ? 'View' : 'Edit' }}">
            <img src="{{ asset('admin-redesign/'.$icon) }}" alt="" width="16" height="16">
        </a>
    @endif
    @if ($items !== [])
        <details class="tb-menu">
            <summary class="tb-row-btn" aria-label="More">
                <img src="{{ asset('admin-redesign/icon-tb-more.svg') }}" alt="" width="16" height="16">
            </summary>
            <menu>
                @foreach ($items as $item)
                    <li>
                        @if (filled($item['href'] ?? null))
                            <a href="{{ $item['href'] }}" @if ($item['external'] ?? false) target="_blank" rel="noopener noreferrer" @endif @class(['is-danger' => $item['danger'] ?? false])>
                                @if (filled($item['icon'] ?? null))
                                    <img src="{{ asset('admin-redesign/'.$item['icon']) }}" alt="" width="16" height="16">
                                @endif
                                {{ $item['label'] }}
                            </a>
                        @else
                            <button type="button" wire:click="{{ $item['wire'] }}" @class(['is-danger' => $item['danger'] ?? false])>
                                @if (filled($item['icon'] ?? null))
                                    <img src="{{ asset('admin-redesign/'.$item['icon']) }}" alt="" width="16" height="16">
                                @endif
                                {{ $item['label'] }}
                            </button>
                        @endif
                    </li>
                @endforeach
            </menu>
        </details>
    @endif
</span>
