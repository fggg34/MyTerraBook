<div class="field">
    <label>Heading</label>
    <input type="text" name="content[heading]" value="{{ $content['heading'] ?? '' }}">
</div>
<div class="field">
    <label>Subheading</label>
    <textarea name="content[subheading]">{{ $content['subheading'] ?? '' }}</textarea>
</div>
<div class="field">
    <label>Photo path</label>
    <input type="text" name="content[photo]" value="{{ $content['photo'] ?? '' }}">
</div>
<div class="grid-2">
    <div class="field">
        <label>Badge rating</label>
        <input type="text" name="content[badge][rating]" value="{{ $content['badge']['rating'] ?? '' }}">
    </div>
    <div class="field">
        <label>Badge — bold line</label>
        <input type="text" name="content[badge][reviewBold]" value="{{ $content['badge']['reviewBold'] ?? '' }}" placeholder="12,400+ travellers">
    </div>
    <div class="field">
        <label>Badge — second line</label>
        <input type="text" name="content[badge][reviewRest]" value="{{ $content['badge']['reviewRest'] ?? '' }}" placeholder="who booked with us">
    </div>
</div>

@foreach (['featuresLeft' => 'Left column', 'featuresRight' => 'Right column'] as $key => $label)
    <h4 style="margin:24px 0 12px;">{{ $label }} features</h4>
    @foreach (($content[$key] ?? []) as $i => $feature)
        <div style="margin-bottom:16px; padding:16px; background:#f8fafc; border-radius:12px;">
            <div class="grid-2">
                <div class="field">
                    <label>Title</label>
                    <input type="text" name="content[{{ $key }}][{{ $i }}][title]" value="{{ $feature['title'] ?? '' }}">
                </div>
                <div class="field">
                    <label>Icon key</label>
                    <input type="text" name="content[{{ $key }}][{{ $i }}][icon]" value="{{ $feature['icon'] ?? '' }}">
                </div>
            </div>
            <div class="field">
                <label>Short description</label>
                <textarea name="content[{{ $key }}][{{ $i }}][description]">{{ $feature['description'] ?? '' }}</textarea>
            </div>
            <div class="field">
                <label>Expanded text (Learn more)</label>
                <textarea name="content[{{ $key }}][{{ $i }}][expandedText]">{{ $feature['expandedText'] ?? '' }}</textarea>
            </div>
        </div>
    @endforeach
@endforeach
