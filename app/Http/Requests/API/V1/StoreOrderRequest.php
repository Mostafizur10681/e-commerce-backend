<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Automatically inject current user ID if not provided
        if ($this->user() && !$this->has('user_id')) {
            $this->merge([
                'user_id' => $this->user()->id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
