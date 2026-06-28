<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class DistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('district');
        if (is_object($id)) {
            $id = $id->id;
        }

        return [
            'division_id' => 'required|integer|exists:divisions,id',
            'district_name' => 'required|string|max:255',
            'district_name_bn' => 'nullable|string|max:255',
            'district_code' => 'required|string|max:50|unique:districts,code,' . ($id ?? 'NULL'),
            'status' => 'nullable|integer|in:0,1',
        ];
    }
}
