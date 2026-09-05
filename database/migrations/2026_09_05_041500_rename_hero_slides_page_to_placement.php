<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hero_slides')) {
            return;
        }

        // Filament/Livewire pagination already uses "page" — rename to placement.
        if (Schema::hasColumn('hero_slides', 'page') && ! Schema::hasColumn('hero_slides', 'placement')) {
            Schema::table('hero_slides', function (Blueprint $table) {
                $table->string('placement')->default('home')->after('is_active');
            });

            DB::table('hero_slides')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    $value = trim((string) ($row->page ?? ''));
                    DB::table('hero_slides')->where('id', $row->id)->update([
                        'placement' => $value !== '' ? $value : 'home',
                    ]);
                }
            });

            Schema::table('hero_slides', function (Blueprint $table) {
                $table->dropColumn('page');
            });

            Schema::table('hero_slides', function (Blueprint $table) {
                $table->index('placement');
            });
        } elseif (! Schema::hasColumn('hero_slides', 'placement')) {
            Schema::table('hero_slides', function (Blueprint $table) {
                $table->string('placement')->default('home')->after('is_active');
                $table->index('placement');
            });
        }

        DB::table('hero_slides')
            ->where(function ($query) {
                $query->whereNull('placement')->orWhere('placement', '');
            })
            ->update(['placement' => 'home']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('hero_slides')) {
            return;
        }

        if (Schema::hasColumn('hero_slides', 'placement') && ! Schema::hasColumn('hero_slides', 'page')) {
            Schema::table('hero_slides', function (Blueprint $table) {
                $table->string('page')->default('home')->after('is_active');
            });

            DB::table('hero_slides')->orderBy('id')->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('hero_slides')->where('id', $row->id)->update([
                        'page' => $row->placement ?: 'home',
                    ]);
                }
            });

            Schema::table('hero_slides', function (Blueprint $table) {
                $table->dropColumn('placement');
            });
        }
    }
};
