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
        Schema::create('cryptopay', function (Blueprint $table) {
            $table->id();
            $table->string('payment_id')->nullable()->unique(); // Payment ID from payment gateway (MixPay code, etc.)
            $table->string('transaction_id')->nullable()->unique(); // Transaction hash or ID
            $table->foreignId('diamond_pack_id')->constrained('diamond_packs')->onDelete('cascade');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 10)->default('USD');
            $table->string('payment_method')->nullable(); // e.g., 'mixpay', 'nowpayments'
            $table->enum('status', ['pending', 'paid', 'failed', 'expired', 'refunded'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('payment_data')->nullable(); // Store additional payment gateway response data
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cryptopay');
    }
};
