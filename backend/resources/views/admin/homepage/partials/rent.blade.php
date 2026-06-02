<div class="field">
    <label>Section heading</label>
    <input type="text" name="content[heading]" value="{{ $content['heading'] ?? '' }}">
</div>
<div class="field">
    <label>Section subtitle</label>
    <textarea name="content[subtitle]">{{ $content['subtitle'] ?? '' }}</textarea>
</div>
@foreach (($content['cards'] ?? []) as $i => $card)
    <div style="margin-top:20px; padding-top:20px; border-top:1px solid #e2e7ef;">
        <h4 style="margin-bottom:12px;">Card {{ $i + 1 }}</h4>
        <div class="grid-2">
            <div class="field">
                <label>Name</label>
                <input type="text" name="content[cards][{{ $i }}][name]" value="{{ $card['name'] ?? '' }}">
            </div>
            <div class="field">
                <label>Tagline</label>
                <input type="text" name="content[cards][{{ $i }}][tagline]" value="{{ $card['tagline'] ?? '' }}">
            </div>
        </div>
        <div class="grid-2">
            <div class="field">
                <label>Listing count / price</label>
                <input type="text" name="content[cards][{{ $i }}][listingCount]" value="{{ $card['listingCount'] ?? '' }}">
            </div>
            <div class="field">
                <label>Link URL</label>
                <input type="text" name="content[cards][{{ $i }}][href]" value="{{ $card['href'] ?? '' }}">
            </div>
        </div>
        <div class="field">
            <label>Image alt text</label>
            <input type="text" name="content[cards][{{ $i }}][alt]" value="{{ $card['alt'] ?? '' }}">
        </div>
        <div class="field">
            <label>Image path</label>
            <input type="text" name="content[cards][{{ $i }}][image]" value="{{ $card['image'] ?? '' }}">
        </div>
    </div>
@endforeach
