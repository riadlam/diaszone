<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // If old table exists, rename it and add a receipt column
        if (Schema::hasTable('seller_topup_requests')) {
            Schema::rename('seller_topup_requests', 'wallet_recharge_asks');

            // add receipt column if missing
            if (!Schema::hasColumn('wallet_recharge_asks', 'receipt')) {
                Schema::table('wallet_recharge_asks', function (Blueprint $table) {
                    $table->string('receipt')->nullable()->after('seller_note');
                });
            }

            // add payment fields if missing
            if (!Schema::hasColumn('wallet_recharge_asks', 'payment_type')) {
                Schema::table('wallet_recharge_asks', function (Blueprint $table) {
                    $table->string('payment_type', 50)->nullable()->after('currency');
                });
            }
            if (!Schema::hasColumn('wallet_recharge_asks', 'payment_method')) {
                Schema::table('wallet_recharge_asks', function (Blueprint $table) {
                    $table->string('payment_method', 100)->nullable()->after('payment_type');
                });
            }
            if (!Schema::hasColumn('wallet_recharge_asks', 'phone')) {
                Schema::table('wallet_recharge_asks', function (Blueprint $table) {
                    $table->string('phone', 40)->nullable()->after('payment_method');
                });
            }
        } else {
            // create new table
            Schema::create('wallet_recharge_asks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('seller_id')->index();
                $table->decimal('amount', 12, 2);
                $table->string('currency', 10)->default('DZD');
                $table->string('status', 20)->default('pending');
                $table->text('seller_note')->nullable();
                $table->string('receipt')->nullable();
                $table->text('admin_note')->nullable();
                $table->unsignedBigInteger('transaction_id')->nullable()->index();
                $table->unsignedBigInteger('admin_id')->nullable()->index();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();

                $table->foreign('seller_id')->references('id')->on('sellers')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wallet_recharge_asks')) {
            // rename back to seller_topup_requests if needed
            if (!Schema::hasTable('seller_topup_requests')) {
                Schema::rename('wallet_recharge_asks', 'seller_topup_requests');
            }
            // ensure receipt column removed in reverse migration
            if (Schema::hasTable('seller_topup_requests') && Schema::hasColumn('seller_topup_requests', 'receipt')) {
                Schema::table('seller_topup_requests', function (Blueprint $table) {
                    $table->dropColumn('receipt');
                });
            }
                // ensure payment columns removed
                if (Schema::hasTable('seller_topup_requests') && Schema::hasColumn('seller_topup_requests', 'payment_type')) {
                    Schema::table('seller_topup_requests', function (Blueprint $table) {
                        $table->dropColumn(['payment_type', 'payment_method', 'phone']);
                    });
                }
        }
    }
};
