<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'pc_zone_id' => ['required', 'integer', Rule::exists('pc_zones', 'id')->where('is_active', true)],
            'pc_id' => ['nullable', 'integer', 'exists:pcs,id'],
            'starts_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:30', 'max:720'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
