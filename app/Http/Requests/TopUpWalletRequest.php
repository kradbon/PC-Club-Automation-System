<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TopUpWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:10', 'max:500', 'regex:/^\d+(\.\d{1,2})?$/'],
            'idempotency_key' => ['required', 'uuid'],
        ];
    }
}
