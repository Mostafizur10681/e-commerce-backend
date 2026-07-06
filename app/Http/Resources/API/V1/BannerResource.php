<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'image' => $this->image,
            'badge' => $this->badge,
            'cta_text' => $this->cta_text,
            'cta_link' => $this->cta_link,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'menu_location' => $this->menu_location,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
