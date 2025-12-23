<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the seller_payout_requests table if it exists
        if (Schema::hasTable('seller_payout_requests')) {
            Schema::dropIfExists('seller_payout_requests');
        }
    }

    public function down(): void
    {
        // Recreate the seller_payout_requests table if it does not exist
        if (!Schema::hasTable('seller_payout_requests')) {
            Schema::create('seller_payout_requests', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('seller_id')->constrained('sellers')->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->string('currency', 8)->default('DZD');
                $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
                $table->text('seller_note')->nullable();
                $table->text('admin_note')->nullable();
                $table->unsignedBigInteger('transaction_id')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }
};
