<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrls = [];
        if ($this->relationLoaded('images')) {
            foreach ($this->images as $img) {
                $imageUrls[] = url(Storage::url($img->image_path));
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'SKU' => $this->SKU,
            'stock' => $this->stock,
            'image' => $this->image ? url(Storage::url($this->image)) : null,
            'images' => $imageUrls,
            'status' => (bool) $this->status,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
