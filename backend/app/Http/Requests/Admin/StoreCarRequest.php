<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarRequest extends FormRequest
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
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'transmission' => ['required', 'string', 'max:32'],
            'fuel_type' => ['required', 'string', 'max:32'],
            'seats' => ['required', 'integer', 'min:1'],
            'bags' => ['required', 'integer', 'min:0'],
            'features' => ['nullable', 'array'],
            'availability_status' => ['nullable', 'string', 'max:32'],
            'base_daily_price' => ['required', 'numeric', 'min:0'],
            'base_hourly_price' => ['nullable', 'numeric', 'min:0'],
            'min_rental_hours' => ['nullable', 'integer', 'min:1'],
            'min_rental_days' => ['nullable', 'integer', 'min:1'],
            'thumbnail_path' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
