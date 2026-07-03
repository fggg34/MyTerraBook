<?php

namespace App\Http\Requests\Api;

use App\Models\GuestHouse;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GuestHouseBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'guest_house_slug' => ['required', 'string', 'exists:guest_houses,slug'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests_count' => ['required', 'integer', 'min:1'],
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['required', 'email', 'max:255'],
            'guest_phone' => ['required', 'string', 'max:64'],
            'special_requests' => ['nullable', 'string', 'max:1000'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
            'payment_method' => ['nullable', 'string', 'max:64'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $house = GuestHouse::query()
                ->where('slug', $this->string('guest_house_slug'))
                ->first();

            if ($house === null) {
                return;
            }

            $checkIn = $this->string('check_in')->toString();
            $checkOut = $this->string('check_out')->toString();
            $nights = (int) Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

            if ($nights < $house->min_nights) {
                $validator->errors()->add(
                    'check_out',
                    "Minimum stay is {$house->min_nights} night(s).",
                );
            }

            if ($house->max_nights !== null && $nights > $house->max_nights) {
                $validator->errors()->add(
                    'check_out',
                    "Maximum stay is {$house->max_nights} night(s).",
                );
            }

            if ($this->integer('guests_count') > $house->max_guests) {
                $validator->errors()->add(
                    'guests_count',
                    "Maximum {$house->max_guests} guests allowed.",
                );
            }

        });
    }
}
