@props(['title', 'subtitle' => null, 'image' => null, 'initials' => null, 'round' => false, 'missing' => false, 'glyph' => false])

<span class="tb-entity tb-keep">
    @if (filled($image))
        <img @class(['tb-cover', 'is-round' => $round, 'is-missing' => $missing, 'is-glyph' => $glyph]) src="{{ $image }}" alt="">
    @elseif (filled($initials))
        <span @class(['tb-cover', 'is-round' => $round, 'tb-initials'])>{{ $initials }}</span>
    @else
        <span @class(['tb-cover', 'is-round' => $round, 'is-missing' => $missing, 'is-glyph' => $glyph])></span>
    @endif
    <span>
        <strong>{{ $title }}</strong>
        @if (filled($subtitle))
            <small>{{ $subtitle }}</small>
        @endif
    </span>
</span>
