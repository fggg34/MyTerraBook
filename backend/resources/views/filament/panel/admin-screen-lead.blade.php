@php
    $screen = \App\Support\AdminScreen::current();
    if ($screen && request()->is('admin/global-configuration')) {
        $screen['title'] = match (request()->query('tab', 'shop')) {
            'prices' => 'Prices & payments',
            'views' => 'Views & layout',
            'orders' => 'Orders & company',
            'texts' => 'Conditional texts',
            default => 'Shop & rentals',
        };
    }
    $links = $screen ? \App\Support\AdminScreen::links($screen['nav']) : [];
    $group = null;
@endphp
@if ($screen && ($screen['crumb'] !== '' || $screen['title'] || $screen['description'] || $links !== []))
    <div @class(['tb-lead', 'tb-lead-settings' => $screen['settings']]) @if ($screen['title']) data-replace-title @endif>
        @if ($screen['crumb'] !== '')
            <p class="tb-crumb">{{ $screen['crumb'] }}</p>
        @endif
        @if ($screen['title'])
            <h1 class="tb-title">{{ $screen['title'] }}</h1>
        @endif
        @if ($screen['description'])
            <p class="tb-desc">{{ $screen['description'] }}</p>
        @endif
        @if ($links !== [])
            <nav class="tb-subnav" aria-label="Section">
                @foreach ($links as $link)
                    @if ($link['group'] && $link['group'] !== $group)
                        @php $group = $link['group']; @endphp
                        <span class="tb-subnav-group">{{ $group }}</span>
                    @endif
                    <a href="{{ $link['url'] }}" @class(['is-active' => $link['active']])>{{ $link['label'] }}</a>
                @endforeach
            </nav>
        @endif
    </div>
@endif
