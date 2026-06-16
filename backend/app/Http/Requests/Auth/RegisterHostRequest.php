<?php

namespace App\Http\Requests\Auth;

use App\Support\PricingCurrency;
use Illuminate\Foundation\Http\FormRequest;

class RegisterHostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'currency' => ['nullable', 'string', 'size:3', PricingCurrency::validationRule()],
        ];
    }
}
