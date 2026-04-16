<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('diamond_packs')) {
            return;
        }

        DB::table('diamond_packs')
            ->where('id', '>=', 323)
            ->whereNotNull('price_usd')
            ->update([
                'price' => DB::raw('price_usd * 1.35'),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('diamond_packs')) {
            return;
        }

        DB::table('diamond_packs')
            ->where('id', '>=', 323)
            ->whereNotNull('price_usd')
            ->update([
                'price' => DB::raw('price / 1.35'),
            ]);
    }
};
