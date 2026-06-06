<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\GuestHouseBooking;
use App\Models\User;

class GuestHouseBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHost();
    }

    public function view(User $user, GuestHouseBooking $booking): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $booking->guestHouse?->user_id === $user->id;
    }

    public function updateStatus(User $user, GuestHouseBooking $booking): bool
    {
        return $this->view($user, $booking);
    }
}
