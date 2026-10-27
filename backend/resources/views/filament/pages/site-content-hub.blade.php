<x-filament-panels::page>
  <div class="site-content-editor" wire:key="site-content-editor-{{ $activePageKey }}">
    <aside class="site-content-picker" aria-label="Pages">
      @foreach ($this->pickerGroups() as $group)
        <p class="site-content-picker-label">{{ $group['label'] }}</p>
        <div class="site-content-picker-list">
          @foreach ($group['pages'] as $page)
            <button
              type="button"
              wire:click="switchTab('{{ $page['key'] }}')"
              @class(['is-active' => $activePageKey === $page['key']])
            >{{ $page['label'] }}</button>
          @endforeach
        </div>
      @endforeach
    </aside>

    <div class="site-content-main">
      <div class="site-content-toolbar">
        <div>
          <p class="site-content-eyebrow">Site content / {{ config("site_content.pages.{$activePageKey}.label", 'Page content') }}</p>
          <h2>{{ config("site_content.pages.{$activePageKey}.label", 'Page content') }}</h2>
          <p>{{ $this->editorIntro() }}</p>
        </div>
        @if ($preview = $this->previewUrl())
          <x-filament::button tag="a" :href="$preview" target="_blank" rel="noopener noreferrer" color="gray" icon="heroicon-o-arrow-top-right-on-square">
            View live page
          </x-filament::button>
        @endif
      </div>

      {{ $this->content }}
    </div>
  </div>

  <style>
    .site-content-editor {
      display: grid;
      grid-template-columns: 236px minmax(0, 1fr);
      gap: 28px;
      align-items: start;
      min-width: 0;
    }
    .site-content-picker {
      position: sticky;
      top: 84px;
      max-height: calc(100dvh - 108px);
      overflow: auto;
      padding-right: 4px;
    }
    .site-content-picker-label {
      margin: 16px 0 6px;
      font-family: "IBM Plex Mono", ui-monospace, monospace;
      font-size: 10.5px;
      font-weight: 600;
      line-height: 14px;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #8a99a9;
    }
    .site-content-picker-label:first-child { margin-top: 0; }
    .site-content-picker-list { display: flex; flex-direction: column; gap: 2px; }
    .site-content-picker button {
      width: 100%;
      padding: 8px 10px;
      border: 0;
      border-radius: 8px;
      background: transparent;
      color: #0d1b2a;
      font-family: "IBM Plex Sans", sans-serif;
      font-size: 14px;
      font-weight: 500;
      line-height: 20px;
      text-align: left;
      cursor: pointer;
    }
    .site-content-picker button:hover { background: #f7f9fc; }
    .site-content-picker button.is-active {
      background: #e8eff7;
      color: #041e37;
      font-weight: 600;
    }
    .site-content-toolbar {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 18px;
    }
    .site-content-eyebrow {
      margin: 0 0 4px;
      font-family: "IBM Plex Mono", ui-monospace, monospace;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      color: #8a99a9;
    }
    .site-content-toolbar h2 {
      margin: 0 0 6px;
      font-family: Manrope, sans-serif;
      font-size: 28px;
      font-weight: 800;
      line-height: 36px;
      letter-spacing: -0.5px;
      color: #0d1b2a;
    }
    .site-content-toolbar p { max-width: 42rem; margin: 0; font-size: 14px; line-height: 20px; color: #5b6b7d; }
    .site-content-editor .site-content-card { margin-bottom: 12px; }
    .site-content-editor .site-content-card .fi-section {
      border: 1px solid #dbe3ec;
      border-radius: 12px;
      background: #fff;
      box-shadow: none;
    }
    .site-content-editor .site-content-card .fi-section-header {
      background: #f7f9fc;
      border-bottom: 1px solid #eaf0f5;
    }
    .site-content-editor .site-content-save-bar > .fi-ac {
      z-index: 20;
      padding: 12px 16px;
      border: 1px solid #dbe3ec;
      border-radius: 12px;
      background: #fff;
      box-shadow: 0 8px 24px rgb(4 30 55 / 8%);
    }
    .fi-page:has(.site-content-editor) > .fi-header { display: none; }
    @media (max-width: 960px) {
      .site-content-editor { grid-template-columns: 1fr; }
      .site-content-picker { position: static; max-height: 240px; }
    }
  </style>
</x-filament-panels::page>
