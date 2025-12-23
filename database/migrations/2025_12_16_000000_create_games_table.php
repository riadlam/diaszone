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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('game_type')->unique()->index(); // e.g., 'mobilelegends', 'freefire', 'genshin_impact_genesis_crystals'
            $table->string('name'); // Display name, e.g., 'Mobile Legends', 'Genshin Impact'
            $table->boolean('is_active')->default(true);
            $table->boolean('is_topseller')->default(false);
            $table->boolean('is_giftcard')->default(false);
            $table->boolean('is_newproduct')->default(false);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('is_topseller');
            $table->index('is_giftcard');
            $table->index('is_newproduct');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
