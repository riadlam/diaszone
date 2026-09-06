<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('flexies') || ! Schema::hasColumn('flexies', 'diamond_pack_id')) {
            return;
        }

        Schema::table('flexies', function (Blueprint $table) {
            $table->dropForeign(['diamond_pack_id']);
        });

        Schema::table('flexies', function (Blueprint $table) {
            $table->unsignedBigInteger('diamond_pack_id')->nullable()->change();
            $table->foreign('diamond_pack_id')
                ->references('id')
                ->on('diamond_packs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('flexies') || ! Schema::hasColumn('flexies', 'diamond_pack_id')) {
            return;
        }

        Schema::table('flexies', function (Blueprint $table) {
            $table->dropForeign(['diamond_pack_id']);
        });

        Schema::table('flexies', function (Blueprint $table) {
            $table->unsignedBigInteger('diamond_pack_id')->nullable(false)->change();
            $table->foreign('diamond_pack_id')
                ->references('id')
                ->on('diamond_packs')
                ->cascadeOnDelete();
        });
    }
};
