<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flash_sale_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('game_type', 64);
            $table->string('image_path');
            $table->decimal('original_price_dzd', 12, 2);
            $table->decimal('sale_price_dzd', 12, 2);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'starts_at', 'ends_at']);
            $table->index(['game_type', 'sort_order']);
        });

        Schema::create('flash_sale_offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_offer_id')->constrained('flash_sale_offers')->cascadeOnDelete();
            $table->foreignId('diamond_pack_id')->constrained('diamond_packs')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['flash_sale_offer_id', 'sort_order']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('flash_sale_offer_id')->nullable()->after('diamond_pack_id')->constrained('flash_sale_offers')->nullOnDelete();
            $table->string('flash_sale_name')->nullable()->after('flash_sale_offer_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('flash_sale_offer_id');
            $table->dropColumn(['flash_sale_name']);
        });

        Schema::dropIfExists('flash_sale_offer_items');
        Schema::dropIfExists('flash_sale_offers');
    }
};
