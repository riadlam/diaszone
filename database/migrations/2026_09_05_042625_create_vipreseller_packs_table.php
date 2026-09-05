<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vipreseller_packs')) {
            return;
        }

        Schema::create('vipreseller_packs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('vipreseller_categories')->cascadeOnDelete();
            $table->string('code')->unique()->comment('VIP service code used as service on order');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('product_url')->nullable();
            $table->string('image_path')->nullable();

            // Provider cost tiers (IDR) from VIP API — synced by cron later
            $table->decimal('price_basic', 12, 2)->nullable();
            $table->decimal('price_premium', 12, 2)->nullable();
            $table->decimal('price_special', 12, 2)->nullable();

            // Sell prices (same pattern as diamond_packs)
            $table->decimal('price_dzd', 10, 2)->default(0);
            $table->decimal('base_price_dzd', 10, 2)->nullable();
            $table->decimal('price_usd', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->default(0);

            $table->string('server')->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->string('provider_status')->default('available')->comment('available / empty');

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('category_id');
            $table->index('is_active');
            $table->index('provider_status');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vipreseller_packs');
    }
};
