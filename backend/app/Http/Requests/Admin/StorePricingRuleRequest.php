<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePricingRuleRequest extends FormRequest
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
            'name' => ['nullable', 'string', 'max:255'],
            'rule_kind' => ['required', 'string', 'max:32'],
            'car_id' => ['nullable', 'integer', 'exists:cars,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'time_unit' => ['required', 'in:day,hour'],
            'amount' => ['required', 'numeric'],
            'adjustment' => ['required', 'in:set,multiply,add'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'min_duration_days' => ['nullable', 'integer', 'min:1'],
            'min_duration_hours' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
