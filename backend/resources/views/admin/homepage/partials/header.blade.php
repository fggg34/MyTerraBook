@php($navLinks = $content['navLinks'] ?? [])
<div class="field">
    <label>CTA button label</label>
    <input type="text" name="content[ctaLabel]" value="{{ $content['ctaLabel'] ?? '' }}">
</div>
<div class="field">
    <label>CTA button URL</label>
    <input type="text" name="content[ctaHref]" value="{{ $content['ctaHref'] ?? '' }}">
</div>
<div class="grid-2">
    <div class="field">
        <label>Language label</label>
        <input type="text" name="content[langLabel]" value="{{ $content['langLabel'] ?? '' }}">
    </div>
    <div class="field">
        <label>Currency label</label>
        <input type="text" name="content[currencyLabel]" value="{{ $content['currencyLabel'] ?? '' }}">
    </div>
</div>
<div class="grid-2">
    <div class="field">
        <label>Sign in label</label>
        <input type="text" name="content[signInLabel]" value="{{ $content['signInLabel'] ?? '' }}">
    </div>
    <div class="field">
        <label>Sign in URL</label>
        <input type="text" name="content[signInHref]" value="{{ $content['signInHref'] ?? '' }}">
    </div>
</div>
@foreach ($navLinks as $i => $link)
    <div class="grid-2" style="margin-top:12px; padding-top:12px; border-top:1px solid #e2e7ef;">
        <div class="field">
            <label>Nav link {{ $i + 1 }} label</label>
            <input type="text" name="content[navLinks][{{ $i }}][label]" value="{{ $link['label'] ?? '' }}">
        </div>
        <div class="field">
            <label>Nav link {{ $i + 1 }} URL</label>
            <input type="text" name="content[navLinks][{{ $i }}][href]" value="{{ $link['href'] ?? '' }}">
        </div>
    </div>
@endforeach
