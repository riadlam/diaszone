<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update image_path column to replace 'storage/game-content-images/' with 'storage_public/game-content-images/'
        DB::table('game_images')
            ->where('image_path', 'like', 'storage/game-content-images/%')
            ->update([
                'image_path' => DB::raw("REPLACE(image_path, 'storage/game-content-images/', 'storage_public/game-content-images/')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: Replace 'storage_public/game-content-images/' back to 'storage/game-content-images/'
        DB::table('game_images')
            ->where('image_path', 'like', 'storage_public/game-content-images/%')
            ->update([
                'image_path' => DB::raw("REPLACE(image_path, 'storage_public/game-content-images/', 'storage/game-content-images/')")
            ]);
    }
};
