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
        Schema::create('seller_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']); // credit = top-up, debit = order deduction
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description');
            $table->string('reference_type')->nullable(); // 'order', 'admin_topup', 'refund'
            $table->unsignedBigInteger('reference_id')->nullable(); // Order ID or null for admin top-up
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null'); // Admin who credited
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_wallet_transactions');
    }
};
