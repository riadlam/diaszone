<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders') || Schema::hasColumn('orders', 'save_id')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->string('save_id')->nullable()->after('server_bs');
            $table->string('server')->nullable()->after('save_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders') || ! Schema::hasColumn('orders', 'save_id')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['save_id', 'server']);
        });
    }
};
