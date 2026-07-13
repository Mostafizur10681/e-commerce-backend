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
        $user = $this->user();
        if (!$user && $token = $this->bearerToken()) {
            $model = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            if ($model && $model->tokenable instanceof \App\Models\User) {
                $user = $model->tokenable;
            }
        }

        if ($user && !$this->has('user_id')) {
            $this->merge([
                'user_id' => $user->id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'division' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'thana' => 'required|string|max:255',
            'address' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
