<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:subscriptions,email',
            'status' => 'nullable|string|in:Active,Subscribed,Unsubscribed,Pending',
            'source' => 'nullable|string|in:Website,Checkout,Newsletter Popup,Manual',
            'notes' => 'nullable|string',
        ];
    }
}
