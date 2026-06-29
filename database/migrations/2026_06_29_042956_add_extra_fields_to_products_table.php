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
        Schema::table('products', function (Blueprint $table) {
            $table->string('sub_category')->nullable()->after('category_id');
            $table->string('brand')->nullable()->after('sub_category');
            $table->decimal('tax', 8, 2)->default(0.00)->after('brand');
            $table->decimal('discount', 8, 2)->default(0.00)->after('tax');
            $table->string('unit')->nullable()->after('discount');
            $table->string('stock_status')->default('in-stock')->after('unit');
            $table->boolean('featured')->default(false)->after('stock_status');
            $table->boolean('best_seller')->default(false)->after('featured');
            $table->boolean('organic')->default(false)->after('best_seller');
            $table->boolean('new_arrival')->default(false)->after('organic');
            $table->string('meta_title')->nullable()->after('new_arrival');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'sub_category',
                'brand',
                'tax',
                'discount',
                'unit',
                'stock_status',
                'featured',
                'best_seller',
                'organic',
                'new_arrival',
                'meta_title',
                'meta_description',
                'meta_keywords'
            ]);
        });
    }
};
