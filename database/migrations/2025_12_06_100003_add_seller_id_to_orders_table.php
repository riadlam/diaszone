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
            $table->foreignId('seller_id')->nullable()->after('user_id')->constrained('sellers')->onDelete('set null');
            $table->boolean('wallet_deducted')->default(false)->after('seller_id');
            $table->decimal('seller_cost', 10, 2)->nullable()->after('wallet_deducted'); // Base cost deducted from seller
            $table->decimal('seller_profit', 10, 2)->nullable()->after('seller_cost'); // Seller's profit (custom_price - base_price)
            $table->boolean('is_direct_topup')->default(false)->after('seller_profit'); // Direct top-up by seller
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['seller_id']);
            $table->dropColumn(['seller_id', 'wallet_deducted', 'seller_cost', 'seller_profit', 'is_direct_topup']);
        });
    }
};
