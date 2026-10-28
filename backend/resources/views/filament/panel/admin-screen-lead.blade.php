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
        $screen['description'] = match (request()->query('tab', 'shop')) {
            'prices' => 'How money is shown and charged.',
            'views' => 'How lists, maps and the public site look.',
            'orders' => 'Company details printed on bookings and invoices.',
            default => 'How booking works on the website: availability, search and system switches.',
        };
    }
    if ($screen && request()->is('admin/site-content', 'admin/site-content/*')) {
        $pageKey = (string) request()->query('tab', 'global');
        $screen['title'] = (string) (config('site_content.pages.'.$pageKey.'.label') ?: 'Site content');
        $screen['crumb'] = 'Website · Site content';
        $screen['description'] = match ($pageKey) {
            'global' => 'Logo, colors, header, footer and the other blocks already stored for every page.',
            'home' => 'Copy and media shown on the public homepage.',
            'terms' => 'Legal copy shown to customers at checkout and in the footer.',
            default => 'Copy and media shown on the public website.',
        };
    }
    $links = $screen ? \App\Support\AdminScreen::links($screen['nav']) : [];
    $group = null;
    $aside = is_array($screen) && in_array($screen['nav'] ?? null, ['settings', 'pricing'], true);
@endphp
@if ($screen && ($screen['crumb'] !== '' || $screen['title'] || $screen['description'] || $links !== []))
    <div @class(['tb-lead', 'tb-lead-settings' => $screen['settings'], 'tb-lead-aside' => $aside]) @if ($screen['title']) data-replace-title @endif>
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
