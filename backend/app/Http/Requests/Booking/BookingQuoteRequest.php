<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class BookingQuoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'car_id' => ['required', 'integer', 'exists:cars,id'],
            'pickup_location_id' => ['required', 'integer', 'exists:locations,id'],
            'dropoff_location_id' => ['required', 'integer', 'exists:locations,id'],
            'pickup_at' => ['required', 'date'],
            'dropoff_at' => ['required', 'date', 'after:pickup_at'],
            'coupon_code' => ['nullable', 'string'],
            'extras' => ['nullable', 'array'],
            'extras.*' => ['integer', 'min:1'],
        ];
    }
}
