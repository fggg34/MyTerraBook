<div class="field">
    <label>Heading (desktop)</label>
    <input type="text" name="content[heading]" value="{{ $content['heading'] ?? '' }}">
</div>
<div class="field">
    <label>Subtitle (desktop)</label>
    <textarea name="content[subtitle]">{{ $content['subtitle'] ?? '' }}</textarea>
</div>
<div class="field">
    <label>Background image (desktop)</label>
    <input type="text" name="content[backgroundImage]" value="{{ $content['backgroundImage'] ?? '' }}" placeholder="Upload below or paste URL">
</div>
<div class="field">
    <label>Heading (mobile)</label>
    <input type="text" name="content[mobileHeading]" value="{{ $content['mobileHeading'] ?? '' }}" placeholder="Uses desktop heading if empty">
</div>
<div class="field">
    <label>Subtitle (mobile)</label>
    <textarea name="content[mobileSubtitle]" placeholder="Uses desktop subtitle if empty">{{ $content['mobileSubtitle'] ?? '' }}</textarea>
</div>
<div class="field">
    <label>Background image (mobile)</label>
    <input type="text" name="content[mobileBackgroundImage]" value="{{ $content['mobileBackgroundImage'] ?? '' }}" placeholder="Uses desktop image if empty">
</div>
<div class="grid-2">
    <div class="field">
        <label>Experience label</label>
        <input type="text" name="content[experienceLabel]" value="{{ $content['experienceLabel'] ?? '' }}">
    </div>
    <div class="field">
        <label>Experience placeholder</label>
        <input type="text" name="content[experiencePlaceholder]" value="{{ $content['experiencePlaceholder'] ?? '' }}">
    </div>
</div>
<div class="grid-2">
    <div class="field">
        <label>Start date label</label>
        <input type="text" name="content[startDateLabel]" value="{{ $content['startDateLabel'] ?? '' }}">
    </div>
    <div class="field">
        <label>End date label</label>
        <input type="text" name="content[endDateLabel]" value="{{ $content['endDateLabel'] ?? '' }}">
    </div>
</div>
<div class="grid-2">
    <div class="field">
        <label>Travelers label</label>
        <input type="text" name="content[travelersLabel]" value="{{ $content['travelersLabel'] ?? '' }}">
    </div>
    <div class="field">
        <label>Travelers default value</label>
        <input type="text" name="content[travelersValue]" value="{{ $content['travelersValue'] ?? '' }}">
    </div>
</div>
<div class="field">
    <label>Search button label</label>
    <input type="text" name="content[searchLabel]" value="{{ $content['searchLabel'] ?? '' }}">
</div>
<div class="grid-2">
    <div class="field">
        <label>Footer hint text</label>
        <input type="text" name="content[footerHint]" value="{{ $content['footerHint'] ?? '' }}">
    </div>
    <div class="field">
        <label>Footer link label</label>
        <input type="text" name="content[footerLinkLabel]" value="{{ $content['footerLinkLabel'] ?? '' }}">
    </div>
</div>
<div class="field">
    <label>Footer link URL</label>
    <input type="text" name="content[footerLinkHref]" value="{{ $content['footerLinkHref'] ?? '' }}">
</div>
