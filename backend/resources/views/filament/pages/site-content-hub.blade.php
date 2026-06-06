<x-filament-panels::page>
  <x-filament::tabs class="mb-4">
    @foreach ($this->getTabGroups() as $group)
      <x-filament::tabs.item
        :active="$this->getActiveGroup() === $group"
        wire:click="switchGroup('{{ $group }}')"
        wire:key="site-content-group-{{ str($group)->slug() }}"
      >
        {{ $group }}
      </x-filament::tabs.item>
    @endforeach
  </x-filament::tabs>

  <x-filament::tabs contained class="mb-6">
    @foreach ($this->getActiveGroupTabs() as $tab)
      <x-filament::tabs.item
        :active="$activePageKey === $tab['key']"
        wire:click="switchTab('{{ $tab['key'] }}')"
        wire:key="site-content-page-{{ $tab['key'] }}"
      >
        {{ $tab['label'] }}
      </x-filament::tabs.item>
    @endforeach
  </x-filament::tabs>

  @if ($preview = $this->previewUrl())
    <div class="mb-4">
      <a
        href="{{ $preview }}"
        target="_blank"
        rel="noopener noreferrer"
        class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400"
      >
        Preview this page →
      </a>
    </div>
  @endif

  <div wire:key="site-content-editor-{{ $activePageKey }}">
    {{ $this->content }}
  </div>
</x-filament-panels::page>
