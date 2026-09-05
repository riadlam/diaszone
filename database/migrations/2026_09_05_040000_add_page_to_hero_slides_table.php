<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hero_slides')) {
            return;
        }

        // Prefer placement; keep adding page only for legacy envs that haven't renamed yet.
        if (Schema::hasColumn('hero_slides', 'placement') || Schema::hasColumn('hero_slides', 'page')) {
            return;
        }

        Schema::table('hero_slides', function (Blueprint $table) {
            $table->string('page')->default('home')->after('is_active');
            $table->index('page');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('hero_slides') || ! Schema::hasColumn('hero_slides', 'page')) {
            return;
        }

        Schema::table('hero_slides', function (Blueprint $table) {
            $table->dropIndex(['page']);
            $table->dropColumn('page');
        });
    }
};
