<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $imageUrls = [];
        if ($this->relationLoaded('images')) {
            foreach ($this->images as $img) {
                $path = trim($img->image_path);
                // If stored as a Base64 data URI, return it directly; otherwise build a storage URL.
                if (Str::contains($path, 'data:image')) {
                    // Extract the data URI part if the path includes a storage URL prefix
                    $base64Pos = strpos($path, 'data:image');
                    $imageUrls[] = $base64Pos !== false ? substr($path, $base64Pos) : $path;
                } else {
                    $imageUrls[] = url(Storage::url($path));
                }
            }
        }

        // Determine main image: if the product has a dedicated image column use it; otherwise fallback to first gallery image.
        $mainImage = $this->image;
        if (empty($mainImage) && count($imageUrls) > 0) {
            $mainImage = $imageUrls[0];
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
            // Return the base64 data URI directly if it is stored as such; otherwise build a storage URL.
            // Ensure main image is a clean data URI if applicable
            'image' => $mainImage ? (function ($img) {
                $img = trim($img);
                return Str::contains($img, 'data:image') ? (strpos($img, 'data:image') !== false ? substr($img, strpos($img, 'data:image')) : $img) : url(Storage::url($img));
            })($mainImage) : null,
            'images' => $imageUrls,
            'status' => (bool) $this->status,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
