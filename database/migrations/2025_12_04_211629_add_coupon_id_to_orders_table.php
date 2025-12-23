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
            $table->foreignId('coupon_id')->nullable()->after('status')->constrained('coupons')->onDelete('set null');
            $table->decimal('discount_amount', 10, 2)->nullable()->after('coupon_id');
            $table->decimal('original_price', 10, 2)->nullable()->after('discount_amount');
            $table->decimal('final_price', 10, 2)->nullable()->after('original_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'discount_amount', 'original_price', 'final_price']);
        });
    }
};
