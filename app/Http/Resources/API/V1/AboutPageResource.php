<?php

namespace App\Http\Resources\API\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutPageResource extends JsonResource
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
            'hero_title' => $this->hero_title,
            'hero_subtitle' => $this->hero_subtitle,
            'hero_badge' => $this->hero_badge,
            'story_title' => $this->story_title,
            'story_badge' => $this->story_badge,
            'story_description_1' => $this->story_description_1,
            'story_description_2' => $this->story_description_2,
            'story_since' => $this->story_since,
            'story_points' => $this->story_points ?? [],
            'story_image' => $this->story_image,
            'mission_title' => $this->mission_title,
            'mission_description' => $this->mission_description,
            'vision_title' => $this->vision_title,
            'vision_description' => $this->vision_description,
            'why_choose_badge' => $this->why_choose_badge,
            'why_choose_title' => $this->why_choose_title,
            'why_choose_subtitle' => $this->why_choose_subtitle,
            'features' => $this->features ?? [],
            'stats' => $this->stats ?? [],
            'team_badge' => $this->team_badge,
            'team_title' => $this->team_title,
            'team_subtitle' => $this->team_subtitle,
            'team' => $this->team ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
