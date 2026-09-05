<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('vipreseller_categories')) {
            return;
        }

        Schema::create('vipreseller_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('filter_game')->nullable()->comment('VIP API filter_game for services sync');
            $table->string('image_path')->nullable();
            $table->string('product_url')->nullable()->comment('Storefront deep link e.g. /digital/netflix');
            $table->json('required_fields')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_topseller')->default(false);
            $table->boolean('is_newproduct')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vipreseller_categories');
    }
};
