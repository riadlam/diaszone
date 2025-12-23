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
            $table->string('game_type')->default('mobilelegends')->after('id');
            $table->index('game_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->dropIndex(['game_type']);
            $table->dropColumn('game_type');
        });
    }
};
