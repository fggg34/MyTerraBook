<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\GuestHouse;
use App\Models\User;

class GuestHousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHost();
    }

    public function view(User $user, GuestHouse $guestHouse): bool
    {
        return $user->role === UserRole::Admin || $guestHouse->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Host || $user->role === UserRole::Admin;
    }

    public function update(User $user, GuestHouse $guestHouse): bool
    {
        return $user->role === UserRole::Admin || $guestHouse->user_id === $user->id;
    }

    public function delete(User $user, GuestHouse $guestHouse): bool
    {
        return $user->role === UserRole::Admin || $guestHouse->user_id === $user->id;
    }

    public function submit(User $user, GuestHouse $guestHouse): bool
    {
        return $guestHouse->user_id === $user->id;
    }
}
