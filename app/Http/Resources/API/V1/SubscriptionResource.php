<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name ?? 'Unknown',
            'email' => $this->email,
            'status' => $this->status ?? 'Subscribed',
            'source' => $this->source ?? 'Manual',
            'notes' => $this->notes ?? '',
            'subscriptionDate' => $this->created_at ? $this->created_at->format('Y-m-d') : null,
            'lastActivity' => $this->updated_at ? $this->updated_at->format('Y-m-d') : null,
        ];
    }
}
