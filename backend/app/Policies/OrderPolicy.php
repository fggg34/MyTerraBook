<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHost();
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $order->car?->user_id === $user->id;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->role === UserRole::Admin;
    }
}
