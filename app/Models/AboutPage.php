<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasFactory;

    protected $table = 'about_pages';

    protected $fillable = [
        'hero_title',
        'hero_subtitle',
        'hero_badge',
        'story_title',
        'story_badge',
        'story_description_1',
        'story_description_2',
        'story_since',
        'story_points',
        'story_image',
        'mission_title',
        'mission_description',
        'vision_title',
        'vision_description',
        'why_choose_badge',
        'why_choose_title',
        'why_choose_subtitle',
        'features',
        'stats',
        'team_badge',
        'team_title',
        'team_subtitle',
        'team',
    ];

    protected $casts = [
        'story_points' => 'array',
        'features' => 'array',
        'stats' => 'array',
        'team' => 'array',
    ];
}
