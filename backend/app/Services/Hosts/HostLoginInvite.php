<?php

namespace App\Services\Hosts;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class HostLoginInvite
{
    public function __construct(private readonly EmailService $email) {}

    public function send(User $host): bool
    {
        if ($host->role !== UserRole::Host) {
            throw new InvalidArgumentException('Only a host account can receive a host login.');
        }

        $password = Str::password(12, symbols: false);
        $previousHash = $host->getRawOriginal('password');
        $previousVerified = $host->email_verified_at;
        $host->forceFill([
            'password' => $password,
            'email_verified_at' => $host->email_verified_at ?? now(),
        ])->save();

        $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $sent = $this->email->send('host_login', $host->email, [
            'host_name' => $host->name,
            'host_email' => $host->email,
            'temporary_password' => $password,
            'login_url' => $frontend.'/host/login',
        ]);

        if (! $sent) {
            DB::table('users')->where('id', $host->id)->update([
                'password' => $previousHash,
                'email_verified_at' => $previousVerified,
            ]);
            $host->refresh();

            return false;
        }

        $host->tokens()->delete();

        return true;
    }
}
