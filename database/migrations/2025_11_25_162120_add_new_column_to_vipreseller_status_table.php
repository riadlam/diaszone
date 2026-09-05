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
        // Table may already be gone (dropped in favor of digiflazz_statuses) on some envs.
        if (! Schema::hasTable('vipreseller_status')) {
            return;
        }

        if (Schema::hasColumn('vipreseller_status', 'balance')) {
            return;
        }

        Schema::table('vipreseller_status', function (Blueprint $table) {
            $table->string('balance', 255)->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('vipreseller_status') || ! Schema::hasColumn('vipreseller_status', 'balance')) {
            return;
        }

        Schema::table('vipreseller_status', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};
