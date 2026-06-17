<x-filament-panels::page>
    <style>
        .et-card {
            border: 1px solid #d6dce5;
            border-radius: 0.6rem;
            background: #ffffff;
            padding: 1.1rem 1.25rem;
            margin-bottom: 1rem;
            max-width: 640px;
        }
        .et-card__title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #334e68;
            margin-bottom: 0.35rem;
        }
        .et-card__desc {
            font-size: 0.8rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        .et-field { display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0.85rem; }
        .et-field label { font-size: 0.8rem; font-weight: 600; color: #374151; }
        .et-field .et-hint { font-size: 0.72rem; color: #6b7280; }
        .et-input, .et-select {
            border: 1px solid #cfd7e2;
            border-radius: 0.45rem;
            padding: 0.5rem 0.65rem;
            font-size: 0.875rem;
            background: #ffffff;
            color: #111827;
            width: 100%;
        }
        .et-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.65rem;
            align-items: center;
            margin-top: 0.25rem;
        }
    </style>

    <div class="et-card">
        <div class="et-card__title">Send a test email</div>
        <p class="et-card__desc">
            Choose a template and send a sample with placeholder data to your preferred inbox.
            Disabled templates can still be tested here.
        </p>

        <div class="et-field">
            <label for="test-email">Your email address</label>
            <input
                id="test-email"
                type="email"
                class="et-input"
                wire:model="testEmail"
                placeholder="you@example.com"
            >
            <span class="et-hint">Saved automatically when you send a test.</span>
        </div>

        <div class="et-field">
            <label for="template-key">Email template</label>
            <select id="template-key" class="et-select" wire:model="templateKey">
                @foreach ($this->templateOptions as $key => $name)
                    <option value="{{ $key }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="et-actions">
            <x-filament::button wire:click="sendTest" icon="heroicon-o-paper-airplane">
                Send test email
            </x-filament::button>
            <x-filament::button wire:click="saveTestEmail" color="gray" icon="heroicon-o-bookmark">
                Save email only
            </x-filament::button>
        </div>
    </div>
</x-filament-panels::page>
