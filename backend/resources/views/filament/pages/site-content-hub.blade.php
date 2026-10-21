<x-filament-panels::page>
  <div class="site-content-editor" wire:key="site-content-editor-{{ $activePageKey }}">
    <div class="site-content-toolbar">
      <div>
        <p class="site-content-eyebrow">{{ $this->getActiveGroup() }} / Site content</p>
        <h2>{{ config("site_content.pages.{$activePageKey}.label", 'Page content') }}</h2>
        <p>Choose a section to edit. Saving publishes changes across all sections of this page.</p>
      </div>
      @if ($preview = $this->previewUrl())
        <x-filament::button tag="a" :href="$preview" target="_blank" rel="noopener noreferrer" color="gray" icon="heroicon-o-arrow-top-right-on-square">
          View live page
        </x-filament::button>
      @endif
    </div>

    {{ $this->content }}
  </div>

  <style>
    .site-content-editor { min-width: 0; }
    .site-content-editor .site-content-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
    .site-content-editor .site-content-toolbar h2 { margin: .25rem 0; font-size: 1.25rem; font-weight: 700; color: #243b53; }
    .site-content-editor .site-content-toolbar p { max-width: 44rem; font-size: .875rem; color: #52667a; }
    .site-content-editor .site-content-toolbar .site-content-eyebrow { font-size: .75rem; font-weight: 600; letter-spacing: .04em; }
    .site-content-editor .site-content-toolbar > a { flex-shrink: 0; }
    .site-content-editor .site-content-sections { align-items: flex-start; overflow: visible; }
    .site-content-editor .site-content-sections > .fi-tabs { position: sticky; top: 5rem; flex: 0 0 14rem; max-height: calc(100dvh - 12rem); padding: .75rem; border: 0; }
    .site-content-editor .site-content-sections > .fi-tabs .fi-tabs-item { white-space: normal; text-align: start; min-height: 2.75rem; }
    .site-content-editor .site-content-sections > .fi-tabs .fi-tabs-item.fi-active { background: #e8eff5; }
    .site-content-editor .site-content-image-upload { width: min(100%, 18rem); }
    .site-content-editor .site-content-image-upload .filepond--root { margin-bottom: 0; }
    .site-content-editor .site-content-image-upload .filepond--drop-label { min-height: 7.5rem; }
    .site-content-editor .site-content-image-upload .filepond--image-preview { justify-content: center; background: #f8fafc; }
    .site-content-editor .site-content-image-upload .filepond--image-preview-wrapper {
      background-color: #f8fafc;
      background-image:
        linear-gradient(45deg, #e4edf5 25%, transparent 25%),
        linear-gradient(-45deg, #e4edf5 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, #e4edf5 75%),
        linear-gradient(-45deg, transparent 75%, #e4edf5 75%);
      background-size: 16px 16px;
      background-position: 0 0, 0 8px, 8px -8px, -8px 0;
    }
    .site-content-editor .site-content-image-upload .filepond--image-preview-overlay { display: none; }
    .site-content-editor .site-content-sections > .fi-tabs .fi-tabs-item:focus-visible { outline: 2px solid #334e68; outline-offset: 2px; }
    .site-content-editor .site-content-sections > .fi-sc-tabs-tab.fi-active { min-width: 0; margin: 0; padding: 1.5rem; border-left: 1px solid #e2e8f0; }
    .site-content-editor .site-content-save-bar > .fi-ac { z-index: 20; padding: 1rem; border: 1px solid #c5d0dc; border-radius: .75rem; background: #fff; box-shadow: 0 4px 20px rgb(36 59 83 / .12); }
    .site-content-editor .fi-fo-repeater-item { border-radius: .75rem; }
    .site-content-editor .fi-fo-repeater-item-header { background: #f5f8fb; border-radius: .75rem .75rem 0 0; }
    @media (max-width: 1023px) {
      .site-content-editor .site-content-sections { flex-direction: column; align-items: stretch; }
      .site-content-editor .site-content-sections > .fi-tabs { position: static; flex: auto; flex-direction: row; overflow-x: auto; max-height: none; border-bottom: 1px solid #e2e8f0; }
      .site-content-editor .site-content-sections > .fi-tabs .fi-tabs-item { white-space: nowrap; flex-shrink: 0; }
      .site-content-editor .site-content-sections > .fi-sc-tabs-tab.fi-active { border-left: 0; padding: 1rem; }
    }
    @media (max-width: 639px) {
      .site-content-editor .site-content-toolbar { align-items: flex-start; flex-direction: column; }
      .site-content-editor .site-content-save-bar > .fi-ac { padding: .75rem; }
    }
  </style>
</x-filament-panels::page>
