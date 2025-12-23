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
            if (!Schema::hasColumn('diamond_packs', 'is_topseller')) {
                $table->boolean('is_topseller')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('diamond_packs', 'is_giftcard')) {
                $table->boolean('is_giftcard')->default(0)->after('is_topseller');
            }
            if (!Schema::hasColumn('diamond_packs', 'is_newproduct')) {
                $table->boolean('is_newproduct')->default(0)->after('is_giftcard');
            }
            if (!Schema::hasColumn('diamond_packs', 'is_gamecredits')) {
                $table->boolean('is_gamecredits')->default(0)->after('is_newproduct');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->dropColumn(['is_topseller', 'is_giftcard', 'is_newproduct', 'is_gamecredits']);
        });
    }
};
