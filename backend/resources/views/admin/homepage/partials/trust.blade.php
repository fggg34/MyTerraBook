@php($items = $content['items'] ?? [])
@foreach ($items as $i => $item)
    <div style="margin-bottom:20px; padding-bottom:20px; border-bottom:1px solid #e2e7ef;">
        <h4 style="margin-bottom:12px;">Trust item {{ $i + 1 }}</h4>
        <div class="grid-2">
            <div class="field">
                <label>Icon (star|check|shield|phone)</label>
                <input type="text" name="content[items][{{ $i }}][icon]" value="{{ $item['icon'] ?? '' }}">
            </div>
            <div class="field">
                <label>Title</label>
                <input type="text" name="content[items][{{ $i }}][title]" value="{{ $item['title'] ?? '' }}">
            </div>
        </div>
        <div class="field">
            <label>Subtitle</label>
            <input type="text" name="content[items][{{ $i }}][subtitle]" value="{{ $item['subtitle'] ?? '' }}">
        </div>
    </div>
@endforeach
