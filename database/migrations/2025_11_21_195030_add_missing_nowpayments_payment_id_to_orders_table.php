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
        Schema::table('orders', function (Blueprint $table) {
            // Check if column doesn't exist before adding
            if (!Schema::hasColumn('orders', 'nowpayments_payment_id')) {
                $table->string('nowpayments_payment_id')->nullable()->after('flexy_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'nowpayments_payment_id')) {
                $table->dropColumn('nowpayments_payment_id');
            }
        });
    }
};

