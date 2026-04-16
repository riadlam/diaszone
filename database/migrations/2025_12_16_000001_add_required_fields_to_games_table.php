<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('games') || Schema::hasColumn('games', 'required_fields')) {
            return;
        }

        Schema::table('games', function (Blueprint $table) {
            $table->json('required_fields')->nullable()->after('is_newproduct');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('games') || ! Schema::hasColumn('games', 'required_fields')) {
            return;
        }

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('required_fields');
        });
    }
};
