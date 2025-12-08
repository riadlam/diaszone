<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallet_recharge_asks') && Schema::hasColumn('wallet_recharge_asks', 'payment_method')) {
            Schema::table('wallet_recharge_asks', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wallet_recharge_asks') && !Schema::hasColumn('wallet_recharge_asks', 'payment_method')) {
            Schema::table('wallet_recharge_asks', function (Blueprint $table) {
                $table->string('payment_method', 100)->nullable()->after('payment_type');
            });
        }
    }
};
