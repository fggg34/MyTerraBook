@php
    /** @var \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, \App\Models\EmailTemplate>> $groups */
    $categoryTitle = static function (string $category): string {
        return match (strtolower($category)) {
            'account' => 'Account',
            'orders' => 'Orders',
            'bookings' => 'Bookings',
            'listings' => 'Listings',
            'general' => 'General',
            'custom' => 'Custom',
            default => \Illuminate\Support\Str::headline($category),
        };
    };
    $audienceTone = static function (?string $audience): string {
        return match (strtolower((string) $audience)) {
            'staff', 'admin' => 'warn',
            'host' => 'ok',
            default => 'neutral',
        };
    };
@endphp
<div class="tb-email-board">
    @forelse ($groups as $category => $templates)
        <section class="tb-email-group">
            <header>
                <h2>{{ $categoryTitle((string) $category) }}</h2>
                <span>{{ $templates->count() }} {{ $templates->count() === 1 ? 'template' : 'templates' }}</span>
            </header>
            <ul>
                @foreach ($templates as $template)
                    <li>
                        <span class="tb-email-mark" aria-hidden="true"></span>
                        <div class="tb-email-copy">
                            <strong>{{ $template->name }}</strong>
                            <p>{{ $template->subject }}</p>
                        </div>
                        <span class="tb-pill tb-pill--{{ $audienceTone($template->audience) }}">{{ \Illuminate\Support\Str::headline((string) $template->audience) }}</span>
                        <button
                            type="button"
                            class="tb-toggle {{ $template->is_enabled ? 'is-on' : '' }}"
                            wire:click="toggleTemplate({{ $template->id }})"
                            aria-pressed="{{ $template->is_enabled ? 'true' : 'false' }}"
                            aria-label="{{ $template->is_enabled ? 'Disable' : 'Enable' }} {{ $template->name }}"
                        >
                            <span></span>
                        </button>
                        <a href="{{ \App\Filament\Resources\EmailTemplates\EmailTemplateResource::getUrl('edit', ['record' => $template]) }}" class="tb-icon-btn">Edit</a>
                    </li>
                @endforeach
            </ul>
        </section>
    @empty
        <p class="tb-side-muted">No e-mail templates yet. Install the defaults or add a new template.</p>
    @endforelse
</div>
