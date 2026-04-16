<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_items')) {
            return;
        }

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('diamond_pack_id')->constrained('diamond_packs')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price_dzd', 10, 2);
            $table->decimal('unit_price_usd', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('subtotal_dzd', 10, 2);
            $table->decimal('discount_amount_dzd', 10, 2)->default(0);
            $table->decimal('total_dzd', 10, 2);
            $table->timestamps();

            $table->index('order_id');
            $table->index('diamond_pack_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
