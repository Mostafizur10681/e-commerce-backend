<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $attributeId = $this->route('attribute');
        return [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:attributes,code,' . $attributeId,
            'type' => 'sometimes|required|string|in:text,select,checkbox',
            'values' => 'nullable|array',
        ];
    }
}
