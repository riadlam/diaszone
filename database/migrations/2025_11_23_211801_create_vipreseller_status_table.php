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
        Schema::create('vipreseller_status', function (Blueprint $table) {
            $table->id();
            $table->string('trxid')->nullable()->index()->comment('Transaction ID from VIP Reseller');
            $table->string('data')->nullable()->comment('User ID (data_no)');
            $table->string('zone')->nullable()->comment('Zone ID (data_zone)');
            $table->string('service')->nullable()->comment('Service code');
            $table->enum('status', ['success', 'error', 'waiting'])->default('waiting')->index();
            $table->text('note')->nullable()->comment('Additional notes or error messages');
            $table->decimal('price', 10, 2)->nullable()->comment('Order price');
            $table->text('additional_data')->nullable()->comment('Additional data in JSON format');
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['data', 'zone']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vipreseller_status');
    }
};
