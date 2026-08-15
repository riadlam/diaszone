<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wheel_rewards', function (Blueprint $table) {
            $table->json('image_paths')->nullable()->after('discount_percentage');
        });

        foreach (DB::table('wheel_rewards')->whereNotNull('icon_path')->get(['id', 'icon_path']) as $reward) {
            if ($reward->icon_path === '') {
                continue;
            }

            DB::table('wheel_rewards')
                ->where('id', $reward->id)
                ->update(['image_paths' => json_encode([$reward->icon_path])]);
        }

        Schema::table('wheel_rewards', function (Blueprint $table) {
            $table->dropColumn('icon_path');
        });
    }

    public function down(): void
    {
        Schema::table('wheel_rewards', function (Blueprint $table) {
            $table->string('icon_path')->nullable()->after('discount_percentage');
        });

        foreach (DB::table('wheel_rewards')->whereNotNull('image_paths')->get(['id', 'image_paths']) as $reward) {
            $paths = json_decode((string) $reward->image_paths, true);

            if (! is_array($paths) || $paths === []) {
                continue;
            }

            DB::table('wheel_rewards')
                ->where('id', $reward->id)
                ->update(['icon_path' => reset($paths)]);
        }

        Schema::table('wheel_rewards', function (Blueprint $table) {
            $table->dropColumn('image_paths');
        });
    }
};
