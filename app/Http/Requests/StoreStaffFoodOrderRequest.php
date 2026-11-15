<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreStaffFoodOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('items') && $this->filled('food_item_id')) {
            $this->merge(['items' => [(string) $this->input('food_item_id') => $this->input('item_quantity', 1)]]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->isStaff() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'request_key' => ['required', 'uuid'],
            'fulfillment_method' => ['required', 'in:club,delivery'],
            'items' => ['required', 'array', 'max:100'],
            'items.*' => ['required', 'integer', 'min:0', 'max:12'],
            'food_item_id' => ['nullable', 'integer', 'exists:food_items,id'],
            'item_quantity' => ['nullable', 'integer', 'min:1', 'max:12'],
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
