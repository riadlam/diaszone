<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update price column for packs with id >= 323
        // Calculate new price = price_usd * 1.35 (adding 35%)
        DB::table('diamond_packs')
            ->where('id', '>=', 323)
            ->whereNotNull('price_usd')
            ->update([
                'price' => DB::raw('price_usd * 1.35')
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert: Calculate original price = price / 1.35
        // Note: This is an approximation and may not be exact due to rounding
        DB::table('diamond_packs')
            ->where('id', '>=', 323)
            ->whereNotNull('price_usd')
            ->update([
                'price' => DB::raw('price / 1.35')
            ]);
    }
};
