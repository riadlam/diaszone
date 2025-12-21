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
            $table->string('save_id')->nullable()->after('server_bs'); // Generic user ID for new games (same as user_id)
            $table->string('server')->nullable()->after('save_id'); // Generic server field for new games like Genshin Impact
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['save_id', 'server']);
        });
    }
};
