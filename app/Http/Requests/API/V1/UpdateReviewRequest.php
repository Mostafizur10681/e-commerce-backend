<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'status' => 'sometimes|required|boolean',
            'image_path' => 'nullable|string',
        ];
    }
}
