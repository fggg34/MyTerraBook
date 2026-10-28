@props(['title'])

<div class="tb-sheet-host" x-data="{ open: false }">
    <span class="tb-m-only" x-on:click="open = true">{{ $trigger }}</span>
    <div class="tb-sheet" x-show="open" x-cloak x-on:keydown.escape.window="open = false">
        <button type="button" class="tb-sheet-backdrop" x-on:click="open = false" aria-label="Close"></button>
        <div class="tb-sheet-panel" role="dialog" aria-modal="true" aria-label="{{ $title }}">
            <header>
                <strong>{{ $title }}</strong>
                <button type="button" x-on:click="open = false">Done</button>
            </header>
            <div class="tb-sheet-body">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
