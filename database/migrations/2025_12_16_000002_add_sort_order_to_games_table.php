<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('games') || Schema::hasColumn('games', 'sort_order')) {
            return;
        }

        Schema::table('games', function (Blueprint $table) {
            $after = Schema::hasColumn('games', 'required_fields') ? 'required_fields' : 'is_newproduct';
            $table->integer('sort_order')->nullable()->default(0)->after($after);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('games') || ! Schema::hasColumn('games', 'sort_order')) {
            return;
        }

        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
