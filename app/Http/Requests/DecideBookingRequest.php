<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecideBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isStaff();
    }

    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:confirm,decline'],
            'reason' => ['nullable', 'string', 'max:1000', 'required_if:decision,decline'],
        ];
    }
}
