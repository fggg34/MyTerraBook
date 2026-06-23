<?php

namespace App\Http\Requests\Auth;

use App\Enums\HostAccountType;
use App\Support\PricingCurrency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'host_account_type' => ['required', Rule::enum(HostAccountType::class)],
            'kennitala' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'currency' => ['nullable', 'string', 'size:3', PricingCurrency::validationRule()],
        ];
    }
}
