<x-filament-panels::page>
    <div class="tb-list">
        <div class="tb-table-card">
            <p class="tb-empty" style="border-bottom:1px solid #e3e8ef">These people still have a site login or an open admin session. Logging them out ends every session immediately.</p>
            @php($logins = app(\App\Services\Admin\ActiveLoginService::class)->list())
            @if ($logins === [])
                <p class="tb-empty">Nobody is logged in right now.</p>
            @else
                <div class="tb-head tb-grid" style="grid-template-columns:minmax(120px,.8fr) minmax(220px,1.6fr) 90px 110px 150px 80px">
                    <span>Name</span><span>E-mail</span><span>Role</span><span>Where</span><span>Last active</span><span></span>
                </div>
                @foreach ($logins as $login)
                    <div class="tb-row tb-grid" style="grid-template-columns:minmax(120px,.8fr) minmax(220px,1.6fr) 90px 110px 150px 80px">
                        <span>{{ $login['name'] }}@if ($login['is_current']) (you)@endif</span>
                        <span>{{ $login['email'] }}</span>
                        <span>{{ \App\Support\AdminTableBadgeColors::humanize((string) $login['role']) }}</span>
                        <span>{{ $login['channels'] }}</span>
                        <span>{{ $login['last_seen'] }}</span>
                        <button type="button" class="tb-text-btn" wire:click="logout({{ $login['id'] }})" wire:confirm="Log this user out on every device?">Log out</button>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</x-filament-panels::page>
