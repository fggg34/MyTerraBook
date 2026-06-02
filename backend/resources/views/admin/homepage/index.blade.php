@extends('admin.homepage.layout')

@section('title', 'Homepage sections')

@section('content')
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h2 style="font-size:28px; margin-bottom:6px;">Homepage sections</h2>
            <p style="color:#5a6b82;">Edit each section of the public homepage.</p>
        </div>
        <a class="btn btn-secondary" href="{{ config('app.frontend_url', '/') }}" target="_blank" rel="noopener">Preview homepage</a>
    </div>

    <div class="section-list">
        @foreach ($sections as $key => $section)
            <div class="section-item">
                <div>
                    <h3>{{ $sectionLabels[$key] ?? ucfirst($key) }}</h3>
                    <p>Key: <code>{{ $key }}</code></p>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <span class="badge {{ $section->is_active ? 'badge-on' : 'badge-off' }}">
                        {{ $section->is_active ? 'Active' : 'Hidden' }}
                    </span>
                    <a class="btn btn-primary" href="{{ route('admin.homepage.edit', $key) }}">Edit</a>
                </div>
            </div>
        @endforeach
    </div>
@endsection
