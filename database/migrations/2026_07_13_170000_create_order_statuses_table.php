<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Seed default order statuses from user's flow
        $defaults = [
            'Order Placed',
            'Processing',
            'Packed',
            'Shipped',
            'Out For Delivery',
            'Delivered',
            'Cancelled'
        ];
        foreach ($defaults as $name) {
            DB::table('order_statuses')->insert([
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => "Default $name order status",
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_statuses');
    }
};
