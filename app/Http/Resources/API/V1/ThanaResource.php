<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThanaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'district_id' => $this->district_id,
            'thana_name' => $this->name,
            'thana_name_bn' => $this->bn_name,
            'thana_code' => $this->code,
            'postal_code' => $this->postal_code,
            'status' => (bool) $this->status,
            'district' => new DistrictResource($this->whenLoaded('district')),
            'division' => new DivisionResource($this->whenLoaded('division')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
