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
            $table->string('region')->nullable()->after('membership_name');
            $table->index('region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diamond_packs', function (Blueprint $table) {
            $table->dropIndex(['region']);
            $table->dropColumn('region');
        });
    }
};
