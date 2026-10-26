<?php

namespace App\Services\Admin;

use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActiveLoginService
{
    /**
     * Users who still have a site login token, or an admin session that has not expired.
     *
     * @return list<array{id: int, name: string, email: string, role: string, channels: string, last_seen: string, is_current: bool}>
     */
    public function list(): array
    {
        $activity = [];

        if (Schema::hasTable('personal_access_tokens')) {
            $tokens = DB::table('personal_access_tokens')
                ->selectRaw('tokenable_id, MAX(COALESCE(last_used_at, created_at)) as last_seen')
                ->where('tokenable_type', (new User)->getMorphClass())
                ->where(function ($query): void {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->groupBy('tokenable_id')
                ->get();

            foreach ($tokens as $token) {
                $id = (int) $token->tokenable_id;
                $activity[$id]['site'] = true;
                $activity[$id]['last_seen'] = $this->later($activity[$id]['last_seen'] ?? null, $token->last_seen);
            }
        }

        if (Schema::hasTable('sessions')) {
            $cutoff = now()->subMinutes((int) config('session.lifetime', 120))->getTimestamp();
            $sessions = DB::table(config('session.table', 'sessions'))
                ->selectRaw('user_id, MAX(last_activity) as last_seen')
                ->whereNotNull('user_id')
                ->where('last_activity', '>=', $cutoff)
                ->groupBy('user_id')
                ->get();

            foreach ($sessions as $session) {
                $id = (int) $session->user_id;
                $activity[$id]['admin'] = true;
                $seen = Carbon::createFromTimestamp((int) $session->last_seen);
                $activity[$id]['last_seen'] = $this->later($activity[$id]['last_seen'] ?? null, $seen);
            }
        }

        if ($activity === []) {
            return [];
        }

        $currentId = auth()->id();

        return User::query()
            ->whereKey(array_keys($activity))
            ->get()
            ->map(function (User $user) use ($activity, $currentId): array {
                $row = $activity[$user->id];
                $channels = match (true) {
                    ($row['site'] ?? false) && ($row['admin'] ?? false) => 'Site and admin',
                    ($row['admin'] ?? false) => 'Admin',
                    default => 'Site',
                };

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => ucfirst($user->role->value),
                    'channels' => $channels,
                    'last_seen' => ($row['last_seen'] ?? now())->timezone(config('app.timezone'))->format('Y-m-d H:i'),
                    'sort_at' => ($row['last_seen'] ?? now())->getTimestamp(),
                    'is_current' => (int) $user->id === (int) $currentId,
                ];
            })
            ->sortByDesc('sort_at')
            ->map(function (array $row): array {
                unset($row['sort_at']);

                return $row;
            })
            ->values()
            ->all();
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();

        if (Schema::hasTable('sessions')) {
            DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
        }
    }

    private function later(mixed $current, mixed $candidate): CarbonInterface
    {
        $next = $candidate instanceof CarbonInterface ? $candidate : Carbon::parse((string) $candidate);

        if (! $current instanceof CarbonInterface) {
            return $next;
        }

        return $next->greaterThan($current) ? $next : $current;
    }
}
