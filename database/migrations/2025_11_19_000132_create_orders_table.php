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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('diamond_pack_id')->constrained('diamond_packs')->onDelete('cascade');
            $table->enum('status', ['pending', 'sending', 'completed', 'refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('user_id_ml')->nullable(); // Mobile Legends User ID
            $table->string('zone_id_ml')->nullable(); // Mobile Legends Zone ID
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
