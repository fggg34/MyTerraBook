<?php

namespace App\Http\Requests\Api;

use App\Models\CustomField;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
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
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:32'],
            'customer_country' => ['nullable', 'string', 'max:4'],
            'rental_options' => ['nullable', 'array'],
            'rental_options.*' => ['integer', 'min:1'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
            'custom_field_values' => ['nullable', 'array'],
            'custom_field_values.*' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $fields = CustomField::query()->where('is_active', true)->get();
            if ($fields->isEmpty()) {
                return;
            }

            $values = $this->input('custom_field_values', []);
            if (! is_array($values)) {
                $validator->errors()->add('custom_field_values', 'Custom field values must be an object.');

                return;
            }

            foreach ($fields as $field) {
                $value = $values[$field->field_key] ?? null;
                $value = is_string($value) ? trim($value) : $value;

                if ($field->is_required && ($value === null || $value === '')) {
                    $validator->errors()->add(
                        "custom_field_values.{$field->field_key}",
                        "{$field->label} is required.",
                    );
                }

                if ($value !== null && $value !== '' && $field->is_email && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add(
                        "custom_field_values.{$field->field_key}",
                        "{$field->label} must be a valid email.",
                    );
                }

                if ($value !== null && $value !== '' && $field->type === 'select') {
                    $options = collect($field->select_options ?? [])->flatten()->all();
                    if ($options !== [] && ! in_array($value, $options, true)) {
                        $validator->errors()->add(
                            "custom_field_values.{$field->field_key}",
                            "{$field->label} has an invalid selection.",
                        );
                    }
                }
            }
        });
    }
}
