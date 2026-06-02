<div class="field">
    <label>Tagline</label>
    <textarea name="content[tagline]">{{ $content['tagline'] ?? '' }}</textarea>
</div>
<div class="field">
    <label>Address block</label>
    <textarea name="content[address]">{{ $content['address'] ?? '' }}</textarea>
</div>
<div class="field">
    <label>Copyright</label>
    <input type="text" name="content[copyright]" value="{{ $content['copyright'] ?? '' }}">
</div>
<div class="grid-2">
    <div class="field">
        <label>Locale label</label>
        <input type="text" name="content[locale]" value="{{ $content['locale'] ?? '' }}">
    </div>
    <div class="field">
        <label>Currency label</label>
        <input type="text" name="content[currency]" value="{{ $content['currency'] ?? '' }}">
    </div>
</div>

@foreach (($content['columns'] ?? []) as $ci => $column)
    <h4 style="margin:20px 0 10px;">Column: {{ $column['title'] ?? ('Column '.$ci) }}</h4>
    <div class="field">
        <label>Column title</label>
        <input type="text" name="content[columns][{{ $ci }}][title]" value="{{ $column['title'] ?? '' }}">
    </div>
    @foreach (($column['links'] ?? []) as $li => $link)
        <div class="grid-2">
            <div class="field">
                <label>Link label</label>
                <input type="text" name="content[columns][{{ $ci }}][links][{{ $li }}][label]" value="{{ $link['label'] ?? '' }}">
            </div>
            <div class="field">
                <label>Link URL</label>
                <input type="text" name="content[columns][{{ $ci }}][links][{{ $li }}][href]" value="{{ $link['href'] ?? '' }}">
            </div>
        </div>
    @endforeach
@endforeach

@foreach (($content['legal'] ?? []) as $i => $link)
    <div class="grid-2">
        <div class="field">
            <label>Legal link {{ $i + 1 }} label</label>
            <input type="text" name="content[legal][{{ $i }}][label]" value="{{ $link['label'] ?? '' }}">
        </div>
        <div class="field">
            <label>Legal link {{ $i + 1 }} URL</label>
            <input type="text" name="content[legal][{{ $i }}][href]" value="{{ $link['href'] ?? '' }}">
        </div>
    </div>
@endforeach
