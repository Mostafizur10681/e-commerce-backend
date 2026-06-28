<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ThanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('thana');
        if (is_object($id)) {
            $id = $id->id;
        }

        return [
            'division_id' => 'required|integer|exists:divisions,id',
            'district_id' => [
                'required',
                'integer',
                'exists:districts,id',
                function ($attribute, $value, $fail) {
                    $divisionId = $this->input('division_id');
                    if ($divisionId) {
                        $exists = DB::table('districts')
                            ->where('id', $value)
                            ->where('division_id', $divisionId)
                            ->exists();
                        if (!$exists) {
                            $fail('The selected district does not belong to the selected division.');
                        }
                    }
                }
            ],
            'thana_name' => 'required|string|max:255',
            'thana_name_bn' => 'nullable|string|max:255',
            'thana_code' => 'required|string|max:50|unique:thanas,code,' . ($id ?? 'NULL'),
            'postal_code' => 'nullable|string|max:50',
            'status' => 'nullable|integer|in:0,1',
        ];
    }
}
