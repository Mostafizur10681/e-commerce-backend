<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'division_id' => $this->division_id,
            'district_name' => $this->name,
            'district_name_bn' => $this->bn_name,
            'district_code' => $this->code,
            'status' => (bool) $this->status,
            'division' => new DivisionResource($this->whenLoaded('division')),
            'thanas_count' => $this->when(isset($this->thanas_count), $this->thanas_count),
            'thanas' => ThanaResource::collection($this->whenLoaded('thanas')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
