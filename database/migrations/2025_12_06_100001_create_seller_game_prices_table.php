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
        Schema::create('seller_game_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->foreignId('diamond_pack_id')->constrained('diamond_packs')->onDelete('cascade');
            $table->decimal('custom_price_dzd', 10, 2); // Seller's custom DZD price
            $table->decimal('custom_price_usd', 10, 2); // Seller's custom USD price
            $table->boolean('is_active')->default(true); // Seller can disable specific packs
            $table->timestamps();

            // Each seller can only have one price per pack
            $table->unique(['seller_id', 'diamond_pack_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_game_prices');
    }
};
