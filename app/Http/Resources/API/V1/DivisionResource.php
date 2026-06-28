<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DivisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'division_name' => $this->name,
            'division_name_bn' => $this->bn_name,
            'division_code' => $this->code,
            'status' => (bool) $this->status,
            'districts_count' => $this->when(isset($this->districts_count), $this->districts_count),
            'districts' => DistrictResource::collection($this->whenLoaded('districts')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
