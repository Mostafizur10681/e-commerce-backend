<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = $this->support_image;
        if ($imageUrl && !str_starts_with($imageUrl, 'http') && !str_starts_with($imageUrl, 'https')) {
            $imageUrl = url('storage/' . $imageUrl);
        }

        return [
            'id' => (string) $this->id,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'business_hours_weekday' => $this->business_hours_weekday,
            'business_hours_weekend' => $this->business_hours_weekend,
            'support_title' => $this->support_title,
            'support_desc' => $this->support_desc,
            'support_phone' => $this->support_phone,
            'support_image' => $imageUrl,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
