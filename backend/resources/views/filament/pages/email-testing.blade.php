@php
    $preview = $this->renderedPreview();
@endphp
<x-filament-panels::page>
    <div class="tb-test">
        <section class="tb-test-card">
            <h2>Send a test e-mail</h2>
            <p>Sample data, sent to your inbox. Disabled templates can be tested too.</p>
            <label for="test-email">Your e-mail address</label>
            <input id="test-email" type="email" wire:model="testEmail" placeholder="you@example.com">
            <span class="tb-hint">Remembered after you send a test.</span>
            <label for="template-key">Email template</label>
            <select id="template-key" wire:model.live="templateKey">
                @foreach ($this->templateOptions as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                @endforeach
            </select>
            <div class="tb-test-actions">
                <button type="button" class="tb-btn tb-btn-md tb-btn-primary" wire:click="sendTest">Send test e-mail</button>
                <button type="button" class="tb-btn tb-btn-md" wire:click="saveTestEmail">Save e-mail only</button>
            </div>
        </section>
        <section class="tb-mail-preview">
            <header>
                <strong>Preview{{ $preview ? ' · '.$preview['name'] : '' }}</strong>
                <span>{{ $preview ? 'Subject: '.$preview['subject'] : 'Choose a template' }}</span>
            </header>
            @if ($preview)
                <iframe title="Email preview" sandbox srcdoc="{{ $preview['html'] }}"></iframe>
            @endif
        </section>
    </div>
</x-filament-panels::page>
