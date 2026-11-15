<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveManagedUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $isUpdate = $user !== null;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(['customer', 'staff', 'admin'])],
            'membership_tier' => ['required', Rule::in(['standard', 'vip'])],
            'account_status' => ['required', Rule::in(['active', 'suspended'])],
            'password' => [$isUpdate ? 'nullable' : 'required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ];
    }
}
