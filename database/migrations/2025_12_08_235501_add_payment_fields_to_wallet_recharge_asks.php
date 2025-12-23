<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallet_recharge_asks')) {
            Schema::table('wallet_recharge_asks', function (Blueprint $table) {
                if (!Schema::hasColumn('wallet_recharge_asks', 'payment_type')) {
                    $table->string('payment_type', 50)->nullable()->after('currency');
                }
                if (!Schema::hasColumn('wallet_recharge_asks', 'payment_method')) {
                    $table->string('payment_method', 100)->nullable()->after('payment_type');
                }
                if (!Schema::hasColumn('wallet_recharge_asks', 'phone')) {
                    $table->string('phone', 40)->nullable()->after('payment_method');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wallet_recharge_asks')) {
            Schema::table('wallet_recharge_asks', function (Blueprint $table) {
                if (Schema::hasColumn('wallet_recharge_asks', 'payment_type')) {
                    $table->dropColumn('payment_type');
                }
                if (Schema::hasColumn('wallet_recharge_asks', 'payment_method')) {
                    $table->dropColumn('payment_method');
                }
                if (Schema::hasColumn('wallet_recharge_asks', 'phone')) {
                    $table->dropColumn('phone');
                }
            });
        }
    }
};
