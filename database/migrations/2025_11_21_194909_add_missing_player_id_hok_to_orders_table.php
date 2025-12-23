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
        Schema::table('orders', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('orders', 'player_id_hok')) {
                $table->string('player_id_hok')->nullable()->after('player_id_pubg'); // Honor of Kings Player ID
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'player_id_hok')) {
                $table->dropColumn('player_id_hok');
            }
        });
    }
};
