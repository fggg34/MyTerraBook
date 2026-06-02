@extends('admin.homepage.layout')

@section('title', $sectionLabel)

@section('content')
    <div style="margin-bottom:24px;">
        <a href="{{ route('admin.homepage.index') }}" style="color:#34629e; text-decoration:none; font-size:14px;">← Back to sections</a>
        <h2 style="font-size:28px; margin-top:12px;">{{ $sectionLabel }}</h2>
    </div>

    <form method="POST" action="{{ route('admin.homepage.update', $section->section_key) }}" class="card" style="margin-bottom:20px;">
        @csrf
        @method('PUT')

        <label style="display:flex; align-items:center; gap:8px; margin-bottom:20px;">
            <input type="checkbox" name="is_active" value="1" @checked($section->is_active)>
            <span style="font-weight:600;">Section visible on homepage</span>
        </label>

        @include('admin.homepage.partials.'.$section->section_key, ['content' => $section->content ?? []])

        <div style="margin-top:24px; display:flex; gap:12px;">
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ config('app.frontend_url', '/') }}" target="_blank" rel="noopener" class="btn btn-secondary">Preview homepage</a>
        </div>
    </form>

    @if (in_array($section->section_key, ['hero', 'rent', 'why'], true))
        <div class="card">
            <h3 style="margin-bottom:12px;">Upload image</h3>
            <form method="POST" action="{{ route('admin.homepage.image', $section->section_key) }}" enctype="multipart/form-data">
                @csrf
                @if ($section->section_key === 'hero')
                    <input type="hidden" name="field" value="backgroundImage">
                @elseif ($section->section_key === 'why')
                    <input type="hidden" name="field" value="photo">
                @endif
                @if ($section->section_key === 'rent')
                    <div class="field">
                        <label>Card index (0–2)</label>
                        <input type="number" name="card_index" min="0" max="2" value="0" required>
                    </div>
                @endif
                <div class="field">
                    <label>Image file</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>
                <button type="submit" class="btn btn-secondary">Upload image</button>
            </form>
        </div>
    @endif
@endsection
