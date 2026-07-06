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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            
            $table->string('customer_name')->after('payment_status');
            $table->string('customer_phone')->after('customer_name');
            $table->string('customer_email')->nullable()->after('customer_phone');
            $table->string('division')->after('customer_email');
            $table->string('district')->after('division');
            $table->string('thana')->after('district');
            $table->text('address')->after('thana');
            
            // Drop old columns
            $table->dropColumn(['shipping_address', 'billing_address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            
            $table->text('shipping_address');
            $table->text('billing_address')->nullable();
            
            $table->dropColumn([
                'customer_name', 
                'customer_phone', 
                'customer_email', 
                'division', 
                'district', 
                'thana', 
                'address'
            ]);
        });
    }
};
