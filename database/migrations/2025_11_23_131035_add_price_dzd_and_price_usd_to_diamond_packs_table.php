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
            $table->decimal('price_dzd', 10, 2)->nullable()->after('price');
            $table->decimal('price_usd', 10, 2)->nullable()->after('price_dzd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->dropColumn(['price_dzd', 'price_usd']);
        });
    }
};
