<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            // Hero Section
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_badge')->nullable();
            
            // Story Section
            $table->string('story_title')->nullable();
            $table->string('story_badge')->nullable();
            $table->text('story_description_1')->nullable();
            $table->text('story_description_2')->nullable();
            $table->string('story_since')->nullable();
            $table->json('story_points')->nullable();
            $table->text('story_image')->nullable();
            
            // Mission & Vision
            $table->string('mission_title')->nullable();
            $table->text('mission_description')->nullable();
            $table->string('vision_title')->nullable();
            $table->text('vision_description')->nullable();
            
            // Why Choose Us
            $table->string('why_choose_badge')->nullable();
            $table->string('why_choose_title')->nullable();
            $table->text('why_choose_subtitle')->nullable();
            $table->json('features')->nullable();
            
            // Stats
            $table->json('stats')->nullable();
            
            // Team
            $table->string('team_badge')->nullable();
            $table->string('team_title')->nullable();
            $table->text('team_subtitle')->nullable();
            $table->json('team')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
