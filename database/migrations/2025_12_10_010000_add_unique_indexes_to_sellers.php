<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Before making unique indexes ensure there are no duplicate non-null values
        // Username already created unique in the initial sellers table migration.
        // Here we only add a unique index for website_url. Check duplicates for website_url only.
        $dupWebsite = DB::table('sellers')
            ->select('website_url', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('website_url')
            ->groupBy('website_url')
            ->having('cnt', '>', 1)
            ->get();

        if ($dupWebsite->isNotEmpty()) {
            $msg = "Cannot apply unique index on sellers.website_url: found duplicate website_url values: " . $dupWebsite->pluck('website_url')->join(', ');
            throw new \RuntimeException($msg);
        }

        Schema::table('sellers', function (Blueprint $table) {
            // Add unique index for website_url to prevent collisions
            // MySQL/Postgres allow multiple NULLs for unique columns — ensure application prevents duplicates
            if (!Schema::hasColumn('sellers', 'username')) return;
            if (!Schema::hasColumn('sellers', 'website_url')) return;

            // Only create indexes if not already present
            // Only add website_url unique index if it does not already exist
            try {
                $table->unique('website_url');
            } catch (\Exception $e) {
                // ignore if index exists or DB doesn't allow
            }
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            // Remove the unique constraints if present
            try { $table->dropUnique(['website_url']); } catch (\Exception $e) {}
        });
    }
};
