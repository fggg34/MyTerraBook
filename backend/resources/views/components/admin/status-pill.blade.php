@props(['tone' => 'info'])

<span {{ $attributes->class(['tb-pill', 'tb-pill-'.$tone]) }}>{{ $slot }}</span>
