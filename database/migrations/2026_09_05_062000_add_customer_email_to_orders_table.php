<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders') || Schema::hasColumn('orders', 'customer_email')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->after('server');
            $table->index('customer_email');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasColumn('orders', 'customer_email')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_email']);
            $table->dropColumn('customer_email');
        });
    }
};
