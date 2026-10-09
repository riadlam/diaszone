<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // VIP Reseller diamond top-ups are Mobile Legends only. Any code on
        // Free Fire / PUBG / other Digiflazz games must not stay set.
        DB::table('diamond_packs')
            ->where('game_type', '!=', 'mobilelegends')
            ->whereNotNull('vip_reseller_code')
            ->update(['vip_reseller_code' => null]);
    }

    public function down(): void
    {
        // Irreversible data cleanup.
    }
};
