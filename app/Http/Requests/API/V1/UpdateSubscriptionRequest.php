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
            'name' => 'nullable|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:subscriptions,email,' . $id,
            'status' => 'sometimes|required|string|in:Active,Subscribed,Unsubscribed,Pending',
            'source' => 'nullable|string|in:Website,Checkout,Newsletter Popup,Manual',
            'notes' => 'nullable|string',
        ];
    }
}
