@php
    use App\Filament\Resources\EmailTemplates\EmailTemplateResource;
    use Illuminate\Support\Str;

    $records = $getRecords();
    $categoryValue = data_get($this->tableFilters, 'category.value');
    $titles = [
        'account' => 'Account',
        'orders' => 'Orders',
        'bookings' => 'Bookings',
        'listings' => 'Listings',
        'general' => 'General',
        'custom' => 'Custom',
    ];
    $groups = $records->groupBy(fn ($template): string => (string) ($template->category ?: 'general'));
@endphp

<div class="tb-list" x-data="{ openFilter: null }">
    <div class="tb-toolbar tb-email-toolbar">
        <label class="tb-search">
            <img src="{{ asset('admin-redesign/icon-tb-search.svg') }}" alt="" width="16" height="16">
            <input type="search" placeholder="Search name or subject…" wire:model.live.debounce.300ms="tableSearch">
        </label>
        <div class="tb-chip-menu">
            <button type="button" @class(['tb-chip', 'is-set' => filled($categoryValue)]) x-on:click="openFilter = openFilter === 'category' ? null : 'category'">
                <img src="{{ asset('admin-redesign/icon-tb-plus.svg') }}" alt="" width="12" height="12">
                {{ $titles[$categoryValue] ?? 'Category' }}
            </button>
            <select x-cloak x-show="openFilter === 'category'" wire:model.live="tableFilters.category.value">
                <option value="">Any</option>
                @foreach ($titles as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
    @forelse ($groups as $category => $templates)
        <section class="tb-email-group">
            <header>
                <strong>{{ $titles[$category] ?? Str::headline((string) $category) }}</strong>
                <span>{{ $templates->count() }} {{ $templates->count() === 1 ? 'template' : 'templates' }}</span>
            </header>
            @foreach ($templates as $template)
                <div class="tb-email-row" wire:key="template-{{ $template->id }}">
                    <span class="tb-email-mark" aria-hidden="true"><x-admin.icon name="mail" :size="16" /></span>
                    <div class="tb-email-copy">
                        <strong>{{ $template->name }}</strong>
                        <p>{{ $template->subject }}</p>
                    </div>
                    <x-admin.status-pill :tone="in_array(strtolower((string) $template->audience), ['staff', 'admin'], true) ? 'warn' : 'info'">{{ Str::headline((string) $template->audience) }}</x-admin.status-pill>
                    <x-admin.switch :on="(bool) $template->is_enabled" wire="toggleTemplate({{ $template->id }})" :label="$template->is_enabled ? 'Enabled' : 'Disabled'" />
                    <a class="tb-icon-btn" href="{{ EmailTemplateResource::getUrl('edit', ['record' => $template]) }}" aria-label="Edit {{ $template->name }}">
                        <x-admin.icon name="pencil" :size="16" />
                    </a>
                </div>
            @endforeach
        </section>
    @empty
        <div class="tb-empty-card"><strong>No e-mail templates yet</strong></div>
    @endforelse
</div>
<x-filament-actions::modals />
