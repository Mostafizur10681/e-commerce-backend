<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('source')->default('Manual')->after('status');
            $table->text('notes')->nullable()->after('source');
        });

        // Change status column from boolean to string
        DB::statement("ALTER TABLE subscriptions MODIFY status VARCHAR(255) DEFAULT 'Subscribed'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['name', 'source', 'notes']);
        });

        // Revert status column
        DB::statement("ALTER TABLE subscriptions MODIFY status TINYINT(1) DEFAULT 1");
    }
};
