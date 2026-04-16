<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sofizpay_cib_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained('orders')->cascadeOnDelete();
            $table->string('transaction_id')->nullable()->index()->comment('SofizPay UUID from create response');
            $table->string('cib_order_number', 64)->nullable()->index()->comment('Value used with cib-transaction-check');
            $table->string('cib_order_id', 128)->nullable()->comment('SATIM mdOrder / orderId from CIB');
            $table->decimal('amount_expected', 12, 2);
            $table->string('status', 32)->default('pending')->index();
            $table->json('create_response')->nullable();
            $table->json('last_check_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('sofizpay_cib_transaction_id')
                ->nullable()
                ->after('chargily_status_id')
                ->constrained('sofizpay_cib_transactions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sofizpay_cib_transaction_id');
        });
        Schema::dropIfExists('sofizpay_cib_transactions');
    }
};
