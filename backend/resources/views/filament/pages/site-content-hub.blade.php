<x-filament-panels::page>
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
