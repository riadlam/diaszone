<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * For environments that already ran the first flash-sale migration with stock columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('flash_sale_offers')) {
            Schema::table('flash_sale_offers', function (Blueprint $table) {
                if (Schema::hasColumn('flash_sale_offers', 'stock_total')) {
                    $table->dropColumn('stock_total');
                }
                if (Schema::hasColumn('flash_sale_offers', 'stock_remaining')) {
                    $table->dropColumn('stock_remaining');
                }
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'flash_stock_consumed')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('flash_stock_consumed');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('flash_sale_offers')) {
            Schema::table('flash_sale_offers', function (Blueprint $table) {
                if (! Schema::hasColumn('flash_sale_offers', 'stock_total')) {
                    $table->unsignedInteger('stock_total')->default(0);
                }
                if (! Schema::hasColumn('flash_sale_offers', 'stock_remaining')) {
                    $table->unsignedInteger('stock_remaining')->default(0);
                }
            });
        }

        if (Schema::hasTable('orders') && ! Schema::hasColumn('orders', 'flash_stock_consumed')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->boolean('flash_stock_consumed')->default(false)->after('flash_sale_name');
            });
        }
    }
};
