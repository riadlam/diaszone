<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('diamond_packs') || Schema::hasColumn('diamond_packs', 'region')) {
            return;
        }

        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->string('region')->nullable()->after('membership_name');
            $table->index('region');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('diamond_packs') || ! Schema::hasColumn('diamond_packs', 'region')) {
            return;
        }

        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->dropIndex(['region']);
            $table->dropColumn('region');
        });
    }
};
