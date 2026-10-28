@props(['tabs', 'active' => 'all'])

<div class="tb-seg" role="tablist" aria-label="Status">
    @foreach ($tabs as $key => $tab)
        <button type="button" wire:click="$set('activeTab', '{{ $key }}')" @class(['is-active' => (string) $active === (string) $key])>
            {{ $tab->getLabel() }}
            @if ($tab->getBadge() !== null)
                <span>{{ $tab->getBadge() }}</span>
            @endif
        </button>
    @endforeach
</div>
