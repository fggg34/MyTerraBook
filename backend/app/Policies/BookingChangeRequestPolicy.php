<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BookingChangeRequest;
use App\Models\GuestHouseBooking;
use App\Models\Order;
use App\Models\User;

class BookingChangeRequestPolicy
{
    public function review(User $user, BookingChangeRequest $request): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if (! $user->isHost()) {
            return false;
        }

        $bookable = $request->bookable;
        if (! $bookable) {
            return false;
        }

        if ($bookable instanceof Order) {
            $bookable->loadMissing('car');

            return (int) $bookable->car?->user_id === (int) $user->id;
        }

        if ($bookable instanceof GuestHouseBooking) {
            $bookable->loadMissing('guestHouse');

            return (int) $bookable->guestHouse?->user_id === (int) $user->id;
        }

        return false;
    }
}
