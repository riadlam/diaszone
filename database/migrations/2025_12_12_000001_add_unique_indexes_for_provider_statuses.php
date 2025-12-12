<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds uniqueness constraints to avoid duplicate provider records and improve matching.
     */
    public function up(): void
    {
        Schema::table('digiflazz_statuses', function (Blueprint $table) {
            // ref_id should be unique per Digiflazz request
            if (!Schema::hasColumn('digiflazz_statuses', 'ref_id')) {
                return;
            }
            try {
                $table->unique('ref_id', 'digiflazz_statuses_ref_id_unique');
            } catch (\Throwable $e) {
                // ignore if index already exists or unsupported in this driver
            }

            try {
                $table->unique('trxid', 'digiflazz_statuses_trxid_unique');
            } catch (\Throwable $e) {
                // ignore
            }
        });

        Schema::table('vipreseller_status', function (Blueprint $table) {
            if (!Schema::hasColumn('vipreseller_status', 'trxid')) {
                return;
            }
            try {
                $table->unique('trxid', 'vipreseller_status_trxid_unique');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('digiflazz_statuses', function (Blueprint $table) {
            $table->dropUnique('digiflazz_statuses_ref_id_unique');
            $table->dropUnique('digiflazz_statuses_trxid_unique');
        });

        Schema::table('vipreseller_status', function (Blueprint $table) {
            $table->dropUnique('vipreseller_status_trxid_unique');
        });
    }
};
