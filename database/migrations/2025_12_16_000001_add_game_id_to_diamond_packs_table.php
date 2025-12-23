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
        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->foreignId('game_id')->nullable()->after('id')->constrained('games')->onDelete('set null');
            $table->index('game_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
            $table->dropIndex(['game_id']);
            $table->dropColumn('game_id');
        });
    }
};
