<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('subscription');
        return [
            'email' => 'sometimes|required|email|max:255|unique:subscriptions,email,' . $id,
            'status' => 'sometimes|required|boolean',
        ];
    }
}
