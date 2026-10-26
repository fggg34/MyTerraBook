<x-filament-panels::page>
    <style>
        .ir-logins {
            border: 1px solid #d9dee7;
            border-radius: 6px;
            background: #fff;
            overflow: hidden;
        }
        .ir-logins p {
            margin: 0;
            padding: 14px 16px;
            font-size: 13px;
            color: #607080;
            border-bottom: 1px solid #e6ebf1;
        }
        .ir-logins table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .ir-logins th,
        .ir-logins td {
            text-align: left;
            padding: 10px 16px;
            border-bottom: 1px solid #eef2f6;
            vertical-align: middle;
        }
        .ir-logins th {
            color: #607080;
            font-weight: 600;
            background: #f7f9fb;
        }
        .ir-logins button {
            border: 1px solid #c9d3df;
            background: #fff;
            color: #9b2335;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }
        .ir-logins .empty {
            padding: 24px 16px;
            color: #607080;
        }
    </style>

    <div class="ir-logins">
        <p>These people still have a site login or an open admin session. Logging them out ends every session immediately.</p>
        @php($logins = app(\App\Services\Admin\ActiveLoginService::class)->list())
        @if ($logins === [])
            <div class="empty">Nobody is logged in right now.</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Where</th>
                        <th>Last active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logins as $login)
                        <tr>
                            <td>{{ $login['name'] }}@if ($login['is_current']) (you)@endif</td>
                            <td>{{ $login['email'] }}</td>
                            <td>{{ $login['role'] }}</td>
                            <td>{{ $login['channels'] }}</td>
                            <td>{{ $login['last_seen'] }}</td>
                            <td>
                                <button
                                    type="button"
                                    wire:click="logout({{ $login['id'] }})"
                                    wire:confirm="Log this user out on every device?"
                                >Log out</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-filament-panels::page>
