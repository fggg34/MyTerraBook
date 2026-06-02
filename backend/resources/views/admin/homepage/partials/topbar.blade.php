<div class="field">
    <label>Banner text</label>
    <input type="text" name="content[text]" value="{{ $content['text'] ?? '' }}">
</div>
<div class="grid-2">
    <div class="field">
        <label>Link label</label>
        <input type="text" name="content[linkLabel]" value="{{ $content['linkLabel'] ?? '' }}">
    </div>
    <div class="field">
        <label>Link URL</label>
        <input type="text" name="content[linkHref]" value="{{ $content['linkHref'] ?? '' }}">
    </div>
</div>
