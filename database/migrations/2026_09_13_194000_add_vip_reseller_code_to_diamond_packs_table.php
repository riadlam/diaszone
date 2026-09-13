<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->string('vip_reseller_code')->nullable()->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->dropColumn('vip_reseller_code');
        });
    }
};
