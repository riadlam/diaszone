<?php

use Illuminate\Database\Migrations\Migration;
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
        // Insert the 3× Weekly Pass if it doesn't already exist (by code)
        $code = 'mlbb-pass-3';
        $exists = DB::table('diamond_packs')->where('code', $code)->exists();
        if (!$exists) {
            DB::table('diamond_packs')->insert([
                'game_type' => 'mobilelegends',
                'name' => '3 Weekly Pass',
                'code' => $code,
                'diamonds' => 55,
                'bonus_diamonds' => 0,
                'price' => 10.00,
                'price_dzd' => 1000,
                'price_usd' => 10.00,
                'discount_percentage' => 0,
                'is_active' => 1,
                'sort_order' => 999,
                'special_quantity' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('diamond_packs')->where('code', 'mlbb-pass-3')->delete();
    }
};
