<?php

namespace App\Support;

use Illuminate\Support\Str;

class BookingConfirmationUrl
{
    public static function generateToken(): string
    {
        do {
            $token = Str::random(48);
        } while (
            \App\Models\Order::query()->where('confirmation_token', $token)->exists()
            || \App\Models\GuestHouseBooking::withTrashed()->where('confirmation_token', $token)->exists()
        );

        return $token;
    }

    public static function forToken(string $token): string
    {
        $frontend = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        return $frontend.'/booking/confirmation/'.$token;
    }

    public static function assignToModel(object $model): void
    {
        if (! empty($model->confirmation_token) && ! empty($model->confirmation_url)) {
            return;
        }

        $token = $model->confirmation_token ?: self::generateToken();
        $model->confirmation_token = $token;
        $model->confirmation_url = self::forToken($token);
    }
}
