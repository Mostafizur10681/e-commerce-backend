<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class DivisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('division');
        if (is_object($id)) {
            $id = $id->id;
        }

        return [
            'division_name' => 'required|string|max:255',
            'division_name_bn' => 'nullable|string|max:255',
            'division_code' => 'required|string|max:50|unique:divisions,code,' . ($id ?? 'NULL'),
            'status' => 'nullable|integer|in:0,1',
        ];
    }
}
