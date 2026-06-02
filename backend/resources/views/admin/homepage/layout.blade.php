<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Homepage CMS') — MyTerraBook Admin</title>
    <style>
        :root {
            --navy: #0f2036;
            --green: #45a06a;
            --line: #e2e7ef;
            --slate: #5a6b82;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #f4f7fb; color: #1d2b40; }
        .admin-shell { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        .admin-sidebar { background: var(--navy); color: #fff; padding: 28px 20px; }
        .admin-sidebar h1 { font-size: 18px; margin-bottom: 8px; }
        .admin-sidebar p { color: #aebdd2; font-size: 13px; margin-bottom: 24px; }
        .admin-sidebar a { display: block; color: #dbe4f0; text-decoration: none; padding: 10px 12px; border-radius: 10px; margin-bottom: 6px; font-size: 14px; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: rgba(255,255,255,.08); color: #fff; }
        .admin-main { padding: 32px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 24px; }
        .flash { padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 14px; }
        .flash-success { background: #ecfdf3; color: #166534; border: 1px solid #bbf7d0; }
        .btn { display: inline-flex; align-items: center; gap: 8px; border: none; border-radius: 999px; padding: 10px 18px; font-weight: 600; cursor: pointer; text-decoration: none; font-size: 14px; }
        .btn-primary { background: var(--green); color: #fff; }
        .btn-secondary { background: #fff; color: var(--navy); border: 1px solid var(--line); }
        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 13px; font-weight: 700; margin-bottom: 6px; color: var(--navy); }
        .field input, .field textarea, .field select { width: 100%; border: 1px solid var(--line); border-radius: 10px; padding: 10px 12px; font: inherit; }
        .field textarea { min-height: 100px; resize: vertical; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .section-list { display: grid; gap: 12px; }
        .section-item { display: flex; justify-content: space-between; align-items: center; padding: 16px 18px; border: 1px solid var(--line); border-radius: 14px; background: #fff; }
        .section-item h3 { font-size: 16px; margin-bottom: 4px; }
        .section-item p { color: var(--slate); font-size: 13px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-on { background: #ecfdf3; color: #166534; }
        .badge-off { background: #fef2f2; color: #991b1b; }
        .preview-link { margin-left: auto; margin-right: 12px; }
        @media (max-width: 900px) {
            .admin-shell { grid-template-columns: 1fr; }
            .grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <h1>Homepage CMS</h1>
        <p>Manage landing page content</p>
        <a href="{{ route('admin.homepage.index') }}" class="{{ request()->routeIs('admin.homepage.index') ? 'active' : '' }}">All sections</a>
        <a href="{{ url('/admin') }}">Filament admin</a>
        <a href="{{ config('app.frontend_url', '/') }}" target="_blank" rel="noopener">Live preview ↗</a>
    </aside>
    <main class="admin-main">
        @if (session('success'))
            <div class="flash flash-success">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
