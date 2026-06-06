<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Car;
use App\Models\User;

class CarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHost();
    }

    public function view(User $user, Car $car): bool
    {
        return $user->role === UserRole::Admin || $car->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Host || $user->role === UserRole::Admin;
    }

    public function update(User $user, Car $car): bool
    {
        return $user->role === UserRole::Admin || $car->user_id === $user->id;
    }

    public function delete(User $user, Car $car): bool
    {
        return $user->role === UserRole::Admin || $car->user_id === $user->id;
    }

    public function submit(User $user, Car $car): bool
    {
        return $car->user_id === $user->id;
    }
}
