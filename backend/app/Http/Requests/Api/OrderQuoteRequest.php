<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class OrderQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'car_id' => ['required', 'integer', 'exists:cars,id'],
            'price_type_id' => ['required', 'integer', 'exists:price_types,id'],
            'pickup_location_id' => ['required', 'integer', 'exists:locations,id'],
            'dropoff_location_id' => ['required', 'integer', 'exists:locations,id'],
            'pickup_at' => ['required', 'date'],
            'dropoff_at' => ['required', 'date', 'after:pickup_at'],
            'rental_options' => ['nullable', 'array'],
            'rental_options.*' => ['integer', 'min:1'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
            'foxie_insurance_id' => ['nullable', 'integer', 'min:1'],
            'partner_insurance_id' => ['nullable', 'string', 'max:64'],
            'partner_extra_ids' => ['nullable', 'array', 'max:20'],
            'partner_extra_ids.*' => ['regex:/^[A-Za-z0-9_-]{1,64}$/'],
        ];
    }
}
