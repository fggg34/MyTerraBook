<x-filament-panels::page>
  <link rel="stylesheet" href="{{ asset('admin-redesign/site-content.css') }}">

  <div @class(['site-content-editor', 'has-preview' => filled($this->previewUrl()), 'site-content-pattern-'.$this->editorPattern()]) data-device="desktop" wire:key="site-content-editor-{{ $activePageKey }}">
    <aside class="site-content-picker" aria-label="Pages">
      <label class="site-content-find">
        <x-admin.icon name="search" :size="16" />
        <input type="search" placeholder="Find a page..." data-site-content-find>
      </label>
      <div class="site-content-picker-scroll">
        @foreach ($this->pickerGroups() as $group)
          <div>
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
          </div>
        @endforeach
      </div>
    </aside>

    <div class="site-content-main">
      <div class="site-content-toolbar">
        <div>
          <p class="site-content-eyebrow">Website · Site content · {{ $this->activePickerGroup() }}</p>
          <h2>{{ config("site_content.pages.{$activePageKey}.label", 'Page content') }}</h2>
          <p>{{ $this->editorIntro() }}</p>
        </div>
        @if ($preview = $this->previewUrl())
          <x-filament::button tag="a" :href="$preview" target="_blank" rel="noopener noreferrer" color="gray" icon="heroicon-o-arrow-top-right-on-square">
            View live page
          </x-filament::button>
        @endif
      </div>

      <p class="site-content-save-note" hidden>You have unsaved changes</p>

      {{ $this->content }}
    </div>

    @if ($preview = $this->previewUrl())
      <aside class="site-content-preview" aria-label="Live preview">
        <header>
          <span>Preview</span>
          <a href="{{ $preview }}" target="_blank" rel="noopener noreferrer">Open</a>
        </header>
        <iframe src="{{ $preview }}" title="Published page"></iframe>
        <p>Published page. Save to update what visitors see.</p>
      </aside>
    @endif
  </div>

  <script>
    document.addEventListener('input', (event) => {
      const field = event.target.closest('.site-content-form');
      if (!field) return;
      field.closest('.site-content-editor')?.querySelector('.site-content-save-note')?.removeAttribute('hidden');
    });
    document.addEventListener('input', (event) => {
      const input = event.target.closest('[data-site-content-find]');
      if (!input) return;
      const query = input.value.trim().toLowerCase();
      input.closest('.site-content-picker')?.querySelectorAll('button').forEach((button) => {
        button.hidden = query !== '' && !button.textContent.toLowerCase().includes(query);
      });
    });
    document.addEventListener('click', (event) => {
      const button = event.target.closest('.site-content-device-tabs button');
      if (!button) return;
      const editor = button.closest('.site-content-editor');
      if (!editor) return;
      editor.dataset.device = button.dataset.device;
      editor.querySelectorAll('.site-content-device-tabs button').forEach((item) => {
        item.classList.toggle('is-active', item.dataset.device === button.dataset.device);
      });
    });
    const mountDeviceTabs = () => {
      document.querySelectorAll('.site-content-card').forEach((card) => {
        const content = card.querySelector('.fi-section-content');
        if (!content || content.querySelector('.site-content-device-tabs')) return;
        if (!content.querySelector('.site-content-device-mobile')) return;
        const bar = document.createElement('div');
        bar.className = 'site-content-device-tabs';
        bar.innerHTML = '<button type="button" data-device="desktop" class="is-active">Desktop</button><button type="button" data-device="mobile">Mobile</button>';
        content.prepend(bar);
      });
    };
    mountDeviceTabs();
    document.addEventListener('click', () => setTimeout(mountDeviceTabs, 50));
    document.addEventListener('livewire:navigated', mountDeviceTabs);
  </script>
</x-filament-panels::page>
