<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'rating' => $this->rating,
            'comment' => $this->comment,
            'status' => (bool) $this->status,
            'image_path' => $this->image_path ? (\Illuminate\Support\Str::contains($this->image_path, 'data:image') ? $this->image_path : url(\Illuminate\Support\Facades\Storage::url($this->image_path))) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
