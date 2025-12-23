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
        // Skip if the table already exists or was renamed to wallet_recharge_asks
        if (Schema::hasTable('seller_topup_requests') || Schema::hasTable('wallet_recharge_asks')) {
            return;
        }
        
        Schema::create('seller_topup_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seller_id')->index();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('DZD');
            $table->string('status', 20)->default('pending');
            $table->text('seller_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable()->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_topup_requests');
    }
};
