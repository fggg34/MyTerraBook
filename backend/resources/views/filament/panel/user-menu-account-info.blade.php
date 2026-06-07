@php
    $user = filament()->auth()->user();
@endphp

@if ($user)
    <div class="fi-user-menu-account-info">
        <p class="fi-user-menu-account-info-name">{{ filament()->getUserName($user) }}</p>
        @if (filled($user->email ?? null))
            <p class="fi-user-menu-account-info-email">{{ $user->email }}</p>
        @endif
    </div>
@endif
