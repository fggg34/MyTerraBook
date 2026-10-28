@php
    $user = auth()->user();
    $name = trim((string) ($user->name ?? 'Admin'));
    $parts = preg_split('/\s+/', $name) ?: [];
    $initials = '';
    foreach (array_slice(array_filter($parts), 0, 2) as $part) {
        $initials .= strtoupper(substr($part, 0, 1));
    }
    $role = $user?->role?->name ?? 'Account';
@endphp
<div class="tb-profile-id">
    <span>{{ $initials !== '' ? $initials : 'A' }}</span>
    <strong>{{ $name }}</strong>
    <small>{{ $role }}</small>
</div>
