<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFoodOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'customer';
    }

    public function rules(): array
    {
        return [
            'request_key' => ['required', 'uuid'],
            'fulfillment_method' => ['required', 'in:club,delivery'],
            'items' => ['required', 'array', 'max:100'],
            'items.*' => ['required', 'integer', 'min:0', 'max:12'],
            'delivery_address' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:1200'],
            'delivery_phone' => ['nullable', 'required_if:fulfillment_method,delivery', 'string', 'max:40'],
            'location_note' => ['nullable', 'string', 'max:255'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (collect($this->input('items', []))->sum(fn ($quantity) => (int) $quantity) < 1) {
                $validator->errors()->add('items', __('ui.food_select_item'));
            }
        }];
    }
}
