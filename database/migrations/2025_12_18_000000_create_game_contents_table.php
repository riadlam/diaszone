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
        Schema::create('game_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->string('currency_name')->nullable(); // e.g., "Diamonds", "Coins", "CP"
            $table->text('about_text')->nullable(); // Main description/about text
            $table->text('instructions_text')->nullable(); // How to find User ID/Zone ID/etc.
            $table->string('id_format')->nullable(); // e.g., "user_id_zone_id", "player_id", "user_id"
            $table->text('how_to_topup')->nullable(); // How to top up steps
            $table->timestamps();
            
            // Ensure one content per game
            $table->unique('game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_contents');
    }
};
