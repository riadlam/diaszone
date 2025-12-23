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
            $table->string('user_id_bs')->nullable()->after('player_id_pubg'); // Blood Strike User ID
            $table->string('server_bs')->nullable()->after('user_id_bs'); // Blood Strike Server
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['user_id_bs', 'server_bs']);
        });
    }
};
