<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure the image_path column can store large Base64 strings.
        DB::statement('ALTER TABLE product_images MODIFY image_path LONGTEXT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original VARCHAR(255) if needed.
        DB::statement('ALTER TABLE product_images MODIFY image_path VARCHAR(255)');
    }
};
