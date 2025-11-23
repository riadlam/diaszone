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
        Schema::create('chargily_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade');
            $table->string('checkout_id')->nullable()->index()->comment('Chargily checkout ID');
            $table->string('event_type')->nullable()->index()->comment('Event type: checkout.paid, checkout.failed, checkout.canceled');
            $table->enum('status', ['pending', 'paid', 'expired', 'canceled', 'failed'])->default('pending')->index();
            $table->decimal('amount', 10, 2)->nullable()->comment('Amount in DZD');
            $table->decimal('fees', 10, 2)->nullable()->comment('Fees charged');
            $table->string('payment_method')->nullable()->comment('edahabia, cib, chargily_app');
            $table->text('metadata')->nullable()->comment('Additional metadata in JSON format');
            $table->text('webhook_data')->nullable()->comment('Full webhook payload in JSON format');
            $table->text('note')->nullable()->comment('Additional notes or error messages');
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chargily_status');
    }
};
