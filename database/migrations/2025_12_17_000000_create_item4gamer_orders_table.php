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
        Schema::create('item4gamer_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('set null');
            $table->foreignId('diamond_pack_id')->nullable()->constrained('diamond_packs')->onDelete('set null');
            $table->unsignedBigInteger('item4gamer_order_id')->nullable()->index(); // Item4Gamer API order ID
            $table->string('status')->nullable(); // pending, completed, cancelled, failed
            $table->integer('quantity')->default(1); // Quantity sent to Item4Gamer
            $table->decimal('total', 10, 2)->nullable(); // Total from Item4Gamer response
            $table->string('currency', 10)->default('USD'); // Currency from Item4Gamer
            $table->string('player_id')->nullable(); // Player/user ID sent to Item4Gamer
            $table->json('additional_data')->nullable(); // Full API response for reference
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->index('order_item_id');
            $table->index('diamond_pack_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item4gamer_orders');
    }
};

