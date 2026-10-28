@props(['on' => false, 'wire' => null, 'label' => 'Toggle'])
<button
    type="button"
    @class(['tb-switch', 'is-on' => $on])
    @if ($wire) wire:click="{{ $wire }}" @endif
    aria-pressed="{{ $on ? 'true' : 'false' }}"
    aria-label="{{ $label }}"
></button>
