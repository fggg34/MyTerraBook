<?php

namespace App\Http\Requests\Me;

use App\Enums\UserRole;
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

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => [
                $user->role === UserRole::Host ? 'required' : 'nullable',
                'string',
                'max:32',
            ],
            'current_password' => [
                Rule::requiredIf(fn () => $this->string('email')->toString() !== $user->email),
                'current_password',
            ],
        ];
    }
}
