<div class="field">
    <label>Banner text (desktop)</label>
    <input type="text" name="content[text]" value="{{ $content['text'] ?? '' }}">
</div>
<div class="field">
    <label>Banner text (mobile)</label>
    <input type="text" name="content[mobileText]" value="{{ $content['mobileText'] ?? '' }}" placeholder="Shorter copy for small screens">
</div>
<div class="grid-2">
    <div class="field">
        <label>Link label</label>
        <input type="text" name="content[linkLabel]" value="{{ $content['linkLabel'] ?? '' }}">
    </div>
    <div class="field">
        <label>Link label (mobile)</label>
        <input type="text" name="content[mobileLinkLabel]" value="{{ $content['mobileLinkLabel'] ?? '' }}" placeholder="Shorter link text for small screens">
    </div>
</div>
<div class="field">
    <label>Link URL</label>
    <input type="text" name="content[linkHref]" value="{{ $content['linkHref'] ?? '' }}">
</div>
