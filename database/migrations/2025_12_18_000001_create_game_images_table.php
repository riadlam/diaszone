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
        Schema::create('game_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
            $table->string('image_path'); // Path to the image file
            $table->string('image_type'); // instruction, example, banner, thumbnail
            $table->integer('display_order')->default(0); // For ordering multiple images
            $table->string('alt_text')->nullable(); // Alt text for accessibility
            $table->string('title')->nullable(); // Optional caption/title
            $table->timestamps();
            
            // Index for efficient queries
            $table->index(['game_id', 'image_type', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_images');
    }
};
