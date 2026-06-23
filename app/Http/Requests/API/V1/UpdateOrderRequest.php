<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|required|string|in:pending,processing,completed,cancelled',
            'payment_status' => 'sometimes|required|string|in:pending,paid,failed,refunded',
        ];
    }
}
