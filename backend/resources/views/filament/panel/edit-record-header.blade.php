@php
    $pills = $pills ?? [];
    $sections = $sections ?? [];
    $summary = $summary ?? [];
    $meta = $meta ?? null;
    $backUrl = $backUrl ?? null;
    $backLabel = $backLabel ?? 'Back';
@endphp
<div class="tb-edit-head">
    @if ($backUrl)
        <a class="tb-edit-back" href="{{ $backUrl }}">{{ $backLabel }}</a>
    @endif
    <div class="tb-edit-head-row">
        <div>
            <h1 class="tb-title">{{ $title }}</h1>
            @if ($pills !== [])
                <div class="tb-edit-pills">
                    @foreach ($pills as $pill)
                        <span class="tb-pill tb-pill--{{ $pill['tone'] ?? 'neutral' }}">{{ $pill['label'] }}</span>
                    @endforeach
                </div>
            @endif
            @if ($meta)
                <p class="tb-edit-meta">{{ $meta }}</p>
            @endif
        </div>
        <div class="tb-edit-head-actions">
            <x-filament-panels::header :actions="$actions" :heading="null" />
        </div>
    </div>
</div>
@if ($summary !== [])
    <div class="tb-summary">
        @foreach ($summary as $item)
            <div class="tb-summary-item">
                <span>{{ $item['label'] }}</span>
                <strong>{{ $item['value'] }}</strong>
                @if (! empty($item['hint']))
                    <small>{{ $item['hint'] }}</small>
                @endif
            </div>
        @endforeach
    </div>
@endif
@if ($sections !== [])
    <nav class="tb-form-toc" aria-label="Sections">
        @foreach ($sections as $id => $label)
            <a href="#{{ $id }}">{{ $label }}</a>
        @endforeach
    </nav>
@endif
