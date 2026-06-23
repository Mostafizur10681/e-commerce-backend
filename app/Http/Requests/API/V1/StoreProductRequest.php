<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'SKU' => 'required|string|unique:products,SKU',
            'stock' => 'nullable|integer|min:0',
            'image_file' => 'nullable|image|max:2048',
            'gallery_files' => 'nullable|array',
            'gallery_files.*' => 'image|max:2048',
            'status' => 'nullable|boolean',
            'category_id' => 'nullable|exists:categories,id',
        ];
    }
}
