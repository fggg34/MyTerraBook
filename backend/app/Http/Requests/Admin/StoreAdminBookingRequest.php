<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Booking\StoreBookingRequest;

class StoreAdminBookingRequest extends StoreBookingRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);
    }
}
