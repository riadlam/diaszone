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
        if (! Schema::hasTable('digiflazz_statuses')) {
            return;
        }

        if (Schema::hasColumn('digiflazz_statuses', 'diamond_pack_id')) {
            return;
        }

        Schema::table('digiflazz_statuses', function (Blueprint $table) {
            $table->foreignId('diamond_pack_id')->nullable()->after('order_id')->constrained('diamond_packs')->onDelete('set null');
            $table->foreignId('order_item_id')->nullable()->after('diamond_pack_id')->constrained('order_items')->onDelete('set null');

            $table->index('diamond_pack_id');
            $table->index('order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('digiflazz_statuses') || ! Schema::hasColumn('digiflazz_statuses', 'diamond_pack_id')) {
            return;
        }

        Schema::table('digiflazz_statuses', function (Blueprint $table) {
            $table->dropForeign(['diamond_pack_id']);
            $table->dropForeign(['order_item_id']);
            $table->dropIndex(['diamond_pack_id']);
            $table->dropIndex(['order_item_id']);
            $table->dropColumn(['diamond_pack_id', 'order_item_id']);
        });
    }
};
