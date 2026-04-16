<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('game_images')) {
            return;
        }

        DB::table('game_images')
            ->where('image_path', 'like', 'storage/game-content-images/%')
            ->update([
                'image_path' => DB::raw("REPLACE(image_path, 'storage/game-content-images/', 'storage_public/game-content-images/')"),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('game_images')) {
            return;
        }

        DB::table('game_images')
            ->where('image_path', 'like', 'storage_public/game-content-images/%')
            ->update([
                'image_path' => DB::raw("REPLACE(image_path, 'storage_public/game-content-images/', 'storage/game-content-images/')"),
            ]);
    }
};
