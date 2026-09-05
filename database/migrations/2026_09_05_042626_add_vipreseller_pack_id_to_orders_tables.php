<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['diamond_pack_id']);
            });

            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('diamond_pack_id')->nullable()->change();
                $table->foreign('diamond_pack_id')
                    ->references('id')
                    ->on('diamond_packs')
                    ->nullOnDelete();
            });

            if (! Schema::hasColumn('order_items', 'vipreseller_pack_id')) {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->foreignId('vipreseller_pack_id')
                        ->nullable()
                        ->after('diamond_pack_id')
                        ->constrained('vipreseller_packs')
                        ->nullOnDelete();
                    $table->index('vipreseller_pack_id');
                });
            }
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropForeign(['diamond_pack_id']);
            });

            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('diamond_pack_id')->nullable()->change();
                $table->foreign('diamond_pack_id')
                    ->references('id')
                    ->on('diamond_packs')
                    ->nullOnDelete();
            });

            if (! Schema::hasColumn('orders', 'vipreseller_pack_id')) {
                Schema::table('orders', function (Blueprint $table) {
                    $table->foreignId('vipreseller_pack_id')
                        ->nullable()
                        ->after('diamond_pack_id')
                        ->constrained('vipreseller_packs')
                        ->nullOnDelete();
                    $table->index('vipreseller_pack_id');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'vipreseller_pack_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vipreseller_pack_id');
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'vipreseller_pack_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('vipreseller_pack_id');
            });
        }
    }
};
