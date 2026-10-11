<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Site routes and JS use `pubgmobile`. Packs were stored as `pubg_mobile`,
        // so player IDs were never mapped and Digiflazz got the wrong customer_no.
        DB::table('diamond_packs')
            ->where('game_type', 'pubg_mobile')
            ->update(['game_type' => 'pubgmobile']);

        if (DB::getSchemaBuilder()->hasTable('games')) {
            DB::table('games')
                ->where('game_type', 'pubg_mobile')
                ->update(['game_type' => 'pubgmobile']);
        }
    }

    public function down(): void
    {
        DB::table('diamond_packs')
            ->where('game_type', 'pubgmobile')
            ->update(['game_type' => 'pubg_mobile']);

        if (DB::getSchemaBuilder()->hasTable('games')) {
            DB::table('games')
                ->where('game_type', 'pubgmobile')
                ->update(['game_type' => 'pubg_mobile']);
        }
    }
};
