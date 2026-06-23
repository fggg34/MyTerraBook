<?php

namespace App\Http\Requests\Me;

use App\Support\PricingCurrency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:32'],
            'current_password' => [
                Rule::requiredIf(fn () => $this->string('email')->toString() !== $user->email),
                'current_password',
            ],
        ];

        if ($user->isHost()) {
            $rules['currency'] = ['required', 'string', 'size:3', PricingCurrency::validationRule()];
            $rules['kennitala'] = ['nullable', 'string', 'max:20'];
        }

        return $rules;
    }
}
