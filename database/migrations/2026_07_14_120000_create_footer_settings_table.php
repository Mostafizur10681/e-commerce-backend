<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('footer_settings', function (Blueprint $table) {
            $table->id();
            $table->string('store_name')->default('FreshMart');
            $table->string('store_icon')->default('🥬');
            $table->text('store_description')->nullable();
            $table->string('copyright_text')->nullable();
            $table->json('social_links')->nullable();   // [{name, icon, url}]
            $table->json('quick_links')->nullable();    // [{label, path}]
            $table->json('service_links')->nullable();  // [{label, path}]
            $table->string('contact_address')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_hours')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('footer_settings');
    }
};
