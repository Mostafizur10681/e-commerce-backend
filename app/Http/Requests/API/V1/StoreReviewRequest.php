<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;


class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if ($this->user() && !$this->has('user_id')) {
            $this->merge([
                'user_id' => $this->user()->id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'user_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'status' => 'nullable|boolean',
            'image_path' => 'nullable|string',
        ];
    }
}
